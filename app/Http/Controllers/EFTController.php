<?php

namespace App\Http\Controllers;

use App\Models\FailEft;
use App\Services\EFTService;
use App\Services\PrintReceipt;
use App\Services\ResponseCodeMapper;
use App\Services\StanGenerator;
use Carbon\Carbon;
use Exception;
use Http;
use Illuminate\Http\Request;
use Session;
use SimpleXMLElement;

class EFTController extends Controller
{
    protected $receipt;

    // Inject the ReceiptService into the controller
    public function __construct(PrintReceipt $receipt)
    {
        $this->receipt = $receipt;
    }

    public function store(Request $request)
    {
        $request->validate([
            "amount" => ["required","numeric", 'min:0'],
            "type" => ["required","string","in:CASH,EFT"],
            "currency" => ["required","string","max:3", "min:3"],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.total_price' => ['required', 'numeric', 'min:0'],
            'change' => ['required', 'numeric', 'min:0'],
            'cash' => ['required', 'numeric', 'min:0'],
            'terminal' => ['required', 'string'],
            'location' => ['required', 'string'],
            //'eft_code' => ['required', 'string']
        ]);



        $eft_currency = "840";
        $currency = $request->currency;
        $amount = convert_minor($request->amount);
        $term_id = get_terminal_id();
        $transaction_id = $this->generateStan();
        $token = Session::get('api_token');
        $url = base_url() . '/eft-success';

        if($currency == "ZIG"){
            $eft_currency = "932";
        }elseif($currency == "ZIG") {
            $eft_currency = "924";
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<Esp:Interface xmlns:Esp="http://www.mosaicsoftware.com/Postilion/eSocket.POS/" Version="1.0">' . "\n" .
                '    <Esp:Transaction CurrencyCode="' . $eft_currency . '" TerminalId="' . $term_id . '" TransactionId="' . $transaction_id . '" Type="PURCHASE" TransactionAmount="' . $amount . '">' . "\n" .
                '        <Esp:PosStructuredData Name="version" Value="2.0.05"/>' . "\n" .
                '    </Esp:Transaction>' . "\n" .
                '</Esp:Interface>';


        try {
            set_time_limit(90);
            $eft = EFTService::getInstance();
            $eft->initializeConnection();
            $eft_response = $eft->sendXMLMessage($xml, $currency);

            if ($eft_response['approved']) {
                $response_array = $this->extractTransactionData($eft_response['raw']);
                //dd($response_array);
                $sale_data = [
                    'amount' => $request->amount,
                    'type' => 'EFT',
                    'currency' => $currency,
                    'items' => $request->items,
                    'transaction_id' => $response_array['transaction_id'],
                    'account' => $response_array['account'],
                    'action_code' => $response_array['action_code'],
                    'authorization_number' => $response_array['authorization_number'],
                    'business_date' => $response_array['business_date'],
                    'date_time' => $response_array['date_time'],
                    'card_number' => $response_array['card_number'],
                    'card_product_name' => $response_array['card_product_name'],
                    'currency_code' => $response_array['currency_code'],
                    'trans_type' => $response_array['type'],
                    'terminal' => $term_id,
                    'location' => get_location()
                ];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ])->post($url, $sale_data);

                if ($response->successful()) {
                    $data = $response->json();
                    $sale = $data['sale'];

                    $cashier_name = Session::get('user')['name'];
                    $datetime = Carbon::parse('2025-02-06T12:16:59.701312Z')->setTimezone('UTC')->toDateTimeString();

                    $this->receipt->printReceipt($sale['reference'], $cashier_name, "CASH", $datetime, $request->items, $sale['currency'], $sale['amount'], 0, 0);

                    return back()->with(['success' => 'Transaction sent successfully']);
                } else {
                    //dd($response->body());
                    $ef = new FailEft();
                    $ef->details = "" . $sale_data;
                    $ef->save();

                    return back()->withErrors(['error' => 'Transaction was received but not saved']);
                }
            } else {
                //dd($eft_response);
                $response_array = $this->extractTransactionData($eft_response['raw']);
                //dd($response_array);
                $sale_data2 = [
                    'amount' => $request->amount,
                    'type' => 'EFT',
                    'currency' => $currency,
                    'items' => $request->items,
                    'transaction_id' => $response_array['transaction_id'],
                    'account' => $response_array['account'],
                    'action_code' => $response_array['action_code'],
                    'authorization_number' => $response_array['authorization_number'],
                    'business_date' => $response_array['business_date'],
                    'date_time' => $response_array['date_time'],
                    'card_number' => $response_array['card_number'],
                    'card_product_name' => $response_array['card_product_name'],
                    'currency_code' => $response_array['currency_code'],
                    'trans_type' => $response_array['type'],
                    'terminal' => $term_id,
                    'location' => get_location()
                ];

                $url2 = base_url() . '/eft-failed';

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ])->post($url2, $sale_data2);

                if ($response->successful()) {
                    $resp = $response->json();
                }

                $response_code_mapper = new ResponseCodeMapper();
                $description = $response_code_mapper->handleResponse($eft_response['response_code']);
                return back()->withErrors(['error' => $description]);
            }

            //dd($eft_response);

        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function closeConnection()
    {
        EFTService::getInstance()->closeConnection();
        return response()->json(['message' => 'Connection closed']);
    }


    public function generateStan()
    {
        $key = 'PURCHASE';

        $stanGenerator = StanGenerator::getInstance();

        $nextStan = $stanGenerator->getNextStan($key);

        return $nextStan;
    }

    function extractTransactionData(string $response): array
    {
        $cleanResponse = preg_replace('/[^\x20-\x7E\x0A\x0D]/', '', $response);

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($cleanResponse, "SimpleXMLElement", LIBXML_NOCDATA);

        if (!$xml) {
            return [];
        }

        // Register namespace if exists
        $namespaces = $xml->getNamespaces(true);
        if (isset($namespaces['Esp'])) {
            $xml->registerXPathNamespace('Esp', $namespaces['Esp']);
        }

        // Extract <Esp:Transaction> element
        $transaction = $xml->xpath('//Esp:Transaction')[0] ?? null;

        if (!$transaction) {
            return [];
        }

        // Extract necessary transaction data
        $transactionData = [
            'account' => (string) ($transaction['Account'] ?? ''),
            'action_code' => (string) ($transaction['ActionCode'] ?? ''),
            'amount_transaction_fee' => (string) ($transaction['AmountTransactionFee'] ?? ''),
            'authorization_number' => (string) ($transaction['AuthorizationNumber'] ?? ''),
            'authorization_profile' => (string) ($transaction['AuthorizationProfile'] ?? ''),
            'business_date' => (string) ($transaction['BusinessDate'] ?? ''),
            'card_number' => (string) ($transaction['CardNumber'] ?? ''),
            'card_product_name' => (string) ($transaction['CardProductName'] ?? ''),
            'card_sequence_number' => (string) ($transaction['CardSequenceNumber'] ?? ''),
            'currency_code' => (string) ($transaction['CurrencyCode'] ?? ''),
            'date_time' => (string) ($transaction['DateTime'] ?? ''),
            'emv_amount' => (string) ($transaction['EmvAmount'] ?? ''),
            'emv_amount_other' => (string) ($transaction['EmvAmountOther'] ?? ''),
            'emv_application_identifier' => (string) ($transaction['EmvApplicationIdentifier'] ?? ''),
            'emv_application_interchange_profile' => (string) ($transaction['EmvApplicationInterchangeProfile'] ?? ''),
            'emv_application_transaction_counter' => (string) ($transaction['EmvApplicationTransactionCounter'] ?? ''),
            'emv_authorization_response_code' => (string) ($transaction['EmvAuthorizationResponseCode'] ?? ''),
            'emv_cryptogram' => (string) ($transaction['EmvCryptogram'] ?? ''),
            'emv_cryptogram_information_data' => (string) ($transaction['EmvCryptogramInformationData'] ?? ''),
            'emv_cvm_results' => (string) ($transaction['EmvCvmResults'] ?? ''),
            'emv_issuer_action_code_default' => (string) ($transaction['EmvIssuerActionCodeDefault'] ?? ''),
            'emv_issuer_action_code_denial' => (string) ($transaction['EmvIssuerActionCodeDenial'] ?? ''),
            'emv_issuer_action_code_online' => (string) ($transaction['EmvIssuerActionCodeOnline'] ?? ''),
            'emv_issuer_application_data' => (string) ($transaction['EmvIssuerApplicationData'] ?? ''),
            'emv_terminal_capabilities' => (string) ($transaction['EmvTerminalCapabilities'] ?? ''),
            'emv_terminal_country_code' => (string) ($transaction['EmvTerminalCountryCode'] ?? ''),
            'emv_terminal_type' => (string) ($transaction['EmvTerminalType'] ?? ''),
            'emv_terminal_verification_result' => (string) ($transaction['EmvTerminalVerificationResult'] ?? ''),
            'emv_transaction_currency_code' => (string) ($transaction['EmvTransactionCurrencyCode'] ?? ''),
            'emv_transaction_date' => (string) ($transaction['EmvTransactionDate'] ?? ''),
            'emv_transaction_status_information' => (string) ($transaction['EmvTransactionStatusInformation'] ?? ''),
            'emv_transaction_type' => (string) ($transaction['EmvTransactionType'] ?? ''),
            'emv_unpredictable_number' => (string) ($transaction['EmvUnpredictableNumber'] ?? ''),
            'expiry_date' => (string) ($transaction['ExpiryDate'] ?? ''),
            'local_date' => (string) ($transaction['LocalDate'] ?? ''),
            'local_time' => (string) ($transaction['LocalTime'] ?? ''),
            'merchant_id' => (string) ($transaction['MerchantId'] ?? ''),
            'message_reason_code' => (string) ($transaction['MessageReasonCode'] ?? ''),
            'pan_entry_mode' => (string) ($transaction['PanEntryMode'] ?? ''),
            'pos_condition' => (string) ($transaction['PosCondition'] ?? ''),
            'pos_data_code' => (string) ($transaction['PosDataCode'] ?? ''),
            'response_code' => (string) ($transaction['ResponseCode'] ?? ''),
            'retrieval_ref_nr' => (string) ($transaction['RetrievalRefNr'] ?? ''),
            'terminal_id' => (string) ($transaction['TerminalId'] ?? ''),
            'track2' => (string) ($transaction['Track2'] ?? ''),
            'transaction_amount' => (string) ($transaction['TransactionAmount'] ?? ''),
            'transaction_id' => (string) ($transaction['TransactionId'] ?? ''),
            'type' => (string) ($transaction['Type'] ?? ''),
        ];

        // Return extracted data
        return $transactionData;
    }

}
