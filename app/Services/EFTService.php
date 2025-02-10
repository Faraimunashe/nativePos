<?php

namespace App\Services;

use Exception;
use SimpleXMLElement;

class EFTService
{
    private static ?EFTService $instance = null;
    private $socket;
    private string $serverIp;
    private int $serverPort;
    private string $termId;
    private string $transCurrency = "";

    private function __construct()
    {
        $this->serverIp = get_socket_ip();
        $this->serverPort = (int) get_socket_port();
        $this->termId = get_terminal_id();
    }

    // Singleton pattern
    public static function getInstance(): EFTService
    {
        if (self::$instance === null) {
            self::$instance = new EFTService();
        }
        return self::$instance;
    }

    public function initializeConnection()
    {
        if (!$this->socket || !is_resource($this->socket)) {
            $this->socket = stream_socket_client(
                "tcp://{$this->serverIp}:{$this->serverPort}",
                $errno,
                $errstr,
                30
            );

            if (!$this->socket) {
                throw new Exception("Failed to connect: $errstr ($errno)");
            }

            //echo "Connected to server: {$this->serverIp}:{$this->serverPort}\n";
            $this->sendInitXML();

            if ($this->receiveAndCheckResponse()) {
                //echo "Connection approved.\n";
            } else {
                //echo "Initialization not approved. Closing connection.\n";
                fclose($this->socket);
                $this->socket = null;
            }
        }
    }

    public function sendCloseXML()
    {
        $closeXml = $this->generateXml('CLOSE');
        $this->sendXML($closeXml);
        //echo "CLOSE XML sent to the server.\n";
    }

    public function sendXMLMessage(string $xml, string $transCurrency)
    {
        if (!$this->socket || !is_resource($this->socket)) {
            //echo "Socket is not connected. Reinitializing connection.\n";
            $this->initializeConnection();
        }

        $this->transCurrency = strtoupper($transCurrency);
        $this->sendXML($xml);
        //echo "XML message sent to the server.\n";

        // Wait for response
        $response = $this->waitForTransactionResponse();

        return $this->parseTransactionResponse($response);
    }

    private function waitForTransactionResponse(): string
    {
        $response = '';
        $timeout = 60; // 60 seconds timeout
        $elapsedTime = 0; // Initialize elapsed time

        while (true) {
            $read = [$this->socket];
            $write = $except = null;
            $tv_sec = 0;
            $tv_usec = 100000; // 100ms timeout

            $numChangedStreams = stream_select($read, $write, $except, $tv_sec, $tv_usec);

            if ($numChangedStreams === false) {
                throw new Exception("Error reading from socket");
            }

            if ($numChangedStreams > 0 && in_array($this->socket, $read)) {
                $chunk = fread($this->socket, 1024);

                if ($chunk === false) {
                    throw new Exception("Error reading from socket");
                }

                $response .= $chunk;

                // Check if the chunk contains <Esp:Transaction> or <Esp:Interface>
                if (strpos($response, '<Esp:Transaction') !== false || strpos($response, '<Esp:Interface') !== false) {
                    //dd($response);
                    // Extract the chunk containing <Esp:Interface> and <Esp:Transaction> (if present)
                    preg_match('/<Esp:Interface[^>]*><Esp:Transaction[^>]*>.*?<\/Esp:Transaction>.*?<\/Esp:Interface>/s', $response, $matches);

                    if (!empty($matches)) {
                        // Clean the response before returning
                        $cleanResponse = preg_replace('/[\x00-\x1F\x80-\x9F]/', '', $matches[0]);
                        $cleanResponse = html_entity_decode($cleanResponse, ENT_NOQUOTES, 'UTF-8');

                        \Log::info('EFT Response: ' . $cleanResponse);

                        return $cleanResponse; // Return both <Esp:Interface> and <Esp:Transaction> chunks
                    }
                }
            }

            // Check timeout
            $elapsedTime += 0.1; // Increment by 100ms
            if ($elapsedTime > $timeout) {
                throw new Exception("Timeout while waiting for transaction response");
            }
        }
    }


    private function parseTransactionResponse(string $response): array
    {
        if (empty($response)) {
            return ['approved' => false, 'message' => 'No response received from the server'];
        }

        // Remove non-printable characters (e.g., BOM, special characters)
        $cleanResponse = preg_replace('/[^\x20-\x7E\x0A\x0D]/', '', $response);

        // Extract all XML parts that contain <Esp:Interface> and <Esp:Transaction> elements
        preg_match_all('/<\?xml[^?]+\?>\s*<Esp:Interface[^>]*>.*?<\/Esp:Interface>.*?<Esp:Transaction[^>]*>.*?<\/Esp:Transaction>/s', $cleanResponse, $matches);
        $xmlParts = $matches[0];

        // Extract non-matching parts (anything that isn't part of the <Esp:Interface> + <Esp:Transaction>)
        $nonMatchingParts = preg_split('/<\?xml[^?]+\?>\s*<Esp:Interface[^>]*>.*?<\/Esp:Interface>.*?<Esp:Transaction[^>]*>.*?<\/Esp:Transaction>/s', $cleanResponse);

        // Filter out empty elements
        $nonMatchingParts = array_filter($nonMatchingParts, fn($part) => trim($part) !== '');

        $transactionXml = $cleanResponse;

        foreach ($xmlParts as $index => $xml) {
            // Trim and clean the XML string by removing non-printable characters
            $cleanedXml = trim($xml);
            $cleanedXml = preg_replace('/[\x00-\x1F\x7F]/', '', $cleanedXml);

            // Create SimpleXMLElement object from the cleaned XML string
            try {
                $xmlObject = new SimpleXMLElement($cleanedXml);

                // Check if <Esp:Transaction> exists in the XML
                if ($xmlObject->xpath('//Esp:Transaction')) {
                    $transactionXml = $cleanedXml;
                }

            } catch (Exception $e) {
                error_log("Failed to parse XML at index {$index}: {$e->getMessage()}");
                continue;
            }
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($transactionXml, "SimpleXMLElement", LIBXML_NOCDATA);

        if (!$xml) {
            return ['approved' => false, 'message' => 'Failed to parse transaction XML', 'raw' => $transactionXml];
        }

        // Register namespace if exists
        $namespaces = $xml->getNamespaces(true);
        if (isset($namespaces['Esp'])) {
            $xml->registerXPathNamespace('Esp', $namespaces['Esp']);
        }

        // Extract the <Esp:Transaction> element
        $transaction = $xml->xpath('//Esp:Transaction')[0] ?? null;

        if (!$transaction) {
            return ['approved' => false, 'message' => 'No valid transaction found', 'raw' => $transactionXml];
        }

        // Extract relevant fields from <Esp:Transaction>
        $actionCode = (string) ($transaction['ActionCode'] ?? 'UNKNOWN');
        $responseCode = (string) ($transaction['ResponseCode'] ?? 'UNKNOWN');
        $transactionId = (string) ($transaction['TransactionId'] ?? 'UNKNOWN');

        // Return the parsed data
        return [
            'approved' => $actionCode === 'APPROVE' || $responseCode === '00',
            'transaction_id' => $transactionId,
            'response_code' => $responseCode,
            'action_code' => $actionCode,
            'raw' => $transactionXml
        ];
    }

    private function sendInitXML()
    {
        $initXml = $this->generateXml('INIT');
        $this->sendXML($initXml);
        //echo "INIT XML sent to the server.\n";
    }

    private function generateXml(string $action): string
    {
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <Esp:Interface xmlns:Esp=\"http://www.mosaicsoftware.com/Postilion/eSocket.POS/\" Version=\"1.0\">
            <Esp:Admin TerminalId=\"{$this->termId}\" Action=\"$action\">
                <Esp:Register Type=\"CALLBACK\" EventId=\"IAN\" />
                <Esp:Register Type=\"CALLBACK\" EventId=\"DATA_REQUIRED\" />
                <Esp:Register Type=\"EVENT\" EventId=\"DEBUG_ALL\" />
            </Esp:Admin>
        </Esp:Interface>";
    }

    private function sendXML(string $xml)
    {
        try{

            $xmlBytes = mb_convert_encoding($xml, 'UTF-8');
            $length = strlen($xmlBytes);
            $header = pack("n", $length); // 2-byte header

            fwrite($this->socket, $header . $xmlBytes);
            fflush($this->socket);
        }catch(Exception $e){
            error_log($e->getMessage());
        }
    }

    private function receiveAndCheckResponse(): bool
    {
        $header = fread($this->socket, 2);
        if (!$header) return false;

        $length = unpack("n", $header)[1];
        $responseData = fread($this->socket, $length);
        //echo "Response from server:\n$responseData\n";

        return strpos($responseData, 'ActionCode="APPROVE"') !== false;
    }

    public function closeConnection()
    {
        if ($this->socket) {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    private function receiveResponse(): string
    {
        $header = fread($this->socket, 2); // Read 2-byte length header
        if (!$header) {
            throw new Exception("No response from server");
        }

        $length = unpack("n", $header)[1]; // Get the length of the message
        $response = fread($this->socket, $length);

        if (!$response) {
            throw new Exception("Failed to read response from server");
        }

        return $response;
    }

}
