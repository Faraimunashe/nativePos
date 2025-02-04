<?php

namespace App\Http\Controllers;

use App\Services\EFTService;
use App\Services\StanGenerator;
use Exception;
use Illuminate\Http\Request;
use SimpleXMLElement;

class EFTController extends Controller
{

    public function store(Request $request)
    {


        //dd($xmlParts[4]);


        //dd($request->all());
        $validated_data = $request->validate([
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
        $amount = $request->amount;
        $term_id = get_terminal_id();
        $transaction_id = $this->generateStan();

        if($currency == "ZIG"){
            $eft_currency = "932";
        }elseif($currency == "ZIG") {
            $eft_currency = "924";
        }
        //$eft_currency = "932";

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<Esp:Interface xmlns:Esp="http://www.mosaicsoftware.com/Postilion/eSocket.POS/" Version="1.0">' . "\n" .
                '    <Esp:Transaction CurrencyCode="' . $eft_currency . '" TerminalId="' . $term_id . '" TransactionId="' . $transaction_id . '" Type="PURCHASE" TransactionAmount="' . $amount . '">' . "\n" .
                '        <Esp:PosStructuredData Name="version" Value="2.0.05"/>' . "\n" .
                '    </Esp:Transaction>' . "\n" .
                '</Esp:Interface>';


        try {
            $eft = EFTService::getInstance();
            $eft->initializeConnection();
            $eft_response = $eft->sendXMLMessage($xml, $currency);

            if ($eft_response['approved']) {
                dd($eft_response);
            } else {
                dd($eft_response);
            }

            //dd($eft_response);
            return back()->with(['success' => 'Transaction sent successfully', 'response'=>$eft_response]);
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
}
