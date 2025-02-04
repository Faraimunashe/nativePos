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

            echo "Connected to server: {$this->serverIp}:{$this->serverPort}\n";
            $this->sendInitXML();

            if ($this->receiveAndCheckResponse()) {
                echo "Connection approved.\n";
            } else {
                echo "Initialization not approved. Closing connection.\n";
                fclose($this->socket);
                $this->socket = null;
            }
        }
    }

    public function sendCloseXML()
    {
        $closeXml = $this->generateXml('CLOSE');
        $this->sendXML($closeXml);
        echo "CLOSE XML sent to the server.\n";
    }

    public function sendXMLMessage(string $xml, string $transCurrency)
    {
        if (!$this->socket || !is_resource($this->socket)) {
            echo "Socket is not connected. Reinitializing connection.\n";
            $this->initializeConnection();
        }

        $this->transCurrency = strtoupper($transCurrency);
        $this->sendXML($xml);
        echo "XML message sent to the server.\n";

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

                // Check if the response contains <Esp:Transaction>
                if (strpos($response, '<Esp:Transaction') !== false) {
                    // Clean response
                    $response = preg_replace('/[\x00-\x1F\x80-\x9F]/', '', $response);
                    $response = html_entity_decode($response, ENT_NOQUOTES, 'UTF-8');

                    \Log::info('EFT Response: ' . $response);

                    return $response;
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


        $transactionXml = $cleanResponse;

        preg_match_all('/<\?xml[^?]+\?>\s*<Esp:Interface[^>]*>.*?<\/Esp:Interface>/s', $cleanResponse, $matches);

        $xmlParts = $matches[0];

        foreach ($xmlParts as $index => $xml) {
            try {
                // Create a SimpleXMLElement object
                $xmlObject = new SimpleXMLElement($xml);

                // Check if the Esp:Transaction element exists
                if ($xmlObject->xpath('//Esp:Transaction')) {
                    $transactionXml = $xml;
                }
            } catch (Exception $e) {
                echo "Error parsing XML document " . ($index + 1) . ": " . $e->getMessage() . "\n";
            }
        }


        libxml_use_internal_errors(true); // Prevent XML parsing warnings
        $xml = simplexml_load_string($transactionXml, "SimpleXMLElement", LIBXML_NOCDATA);

        if (!$xml) {
            return ['approved' => false, 'message' => 'Failed to parse transaction XML', 'raw' => $transactionXml];
        }

        // Register namespace
        $namespaces = $xml->getNamespaces(true);
        if (isset($namespaces['Esp'])) {
            $xml->registerXPathNamespace('Esp', $namespaces['Esp']);
        }

        // Extract transaction data
        $transaction = $xml->xpath('//Esp:Transaction')[0] ?? null;

        if (!$transaction) {
            return ['approved' => false, 'message' => 'No valid transaction found', 'raw' => $transactionXml];
        }

        $actionCode = (string) $transaction['ActionCode'] ?? 'UNKNOWN';
        $responseCode = (string) $transaction['ResponseCode'] ?? 'UNKNOWN';
        $transactionId = (string) $transaction['TransactionId'] ?? 'UNKNOWN';

        return [
            'approved' => $actionCode === 'APPROVE' || $responseCode === '00',
            'transaction_id' => $transactionId,
            'response_code' => $responseCode,
            'action_code' => $actionCode,
            'raw' => $transactionXml
        ];
    }




    // private function parseTransactionResponse(string $response): array
    // {
    //     if (empty($response)) {
    //         return ['approved' => false, 'message' => 'No response received from the server'];
    //     }

    //     // Remove non-printable characters (including extra new lines, spaces, etc.)
    //     $clean_string = preg_replace('/[^\x20-\x7E]/', '', trim($response));

    //     // Remove anything before the first XML declaration
    //     $clean_string = preg_replace('/^.*?(<\?xml\s)/s', '<?xml ', $clean_string);

    //     // Try to load the XML
    //     libxml_use_internal_errors(true); // Enable error handling
    //     $xml = simplexml_load_string($clean_string, "SimpleXMLElement", LIBXML_NOCDATA);

    //     if (!$xml) {
    //         $errors = libxml_get_errors();
    //         libxml_clear_errors();
    //         return [
    //             'approved' => false,
    //             'message' => 'Invalid XML response',
    //             'errors' => array_map(fn($error) => $error->message, $errors),
    //             'raw' => $clean_string
    //         ];
    //     }

    //     $namespaces = $xml->getNamespaces(true);
    //     if (isset($namespaces['Esp'])) {
    //         $xml->registerXPathNamespace('Esp', $namespaces['Esp']);
    //     }

    //     // Extract Transaction Data
    //     $transaction = $xml->xpath('//Esp:Transaction');

    //     if ($transaction) {
    //         $transaction = $transaction[0];
    //         $actionCode = (string) $transaction['ActionCode'] ?? 'UNKNOWN';
    //         $responseCode = (string) $transaction['ResponseCode'] ?? 'UNKNOWN';
    //         $transactionId = (string) $transaction['TransactionId'] ?? 'UNKNOWN';

    //         return [
    //             'approved' => $actionCode === 'APPROVED' || $responseCode === '00',
    //             'message' => "TransactionId: $transactionId, ActionCode: $actionCode, ResponseCode: $responseCode",
    //             'raw' => $clean_string
    //         ];
    //     }

    //     return ['approved' => false, 'message' => 'Unknown response format', 'raw' => $clean_string];
    // }


    private function sendInitXML()
    {
        $initXml = $this->generateXml('INIT');
        $this->sendXML($initXml);
        echo "INIT XML sent to the server.\n";
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
        $xmlBytes = mb_convert_encoding($xml, 'UTF-8');
        $length = strlen($xmlBytes);
        $header = pack("n", $length); // 2-byte header

        fwrite($this->socket, $header . $xmlBytes);
        fflush($this->socket);
    }

    private function receiveAndCheckResponse(): bool
    {
        $header = fread($this->socket, 2);
        if (!$header) return false;

        $length = unpack("n", $header)[1];
        $responseData = fread($this->socket, $length);
        echo "Response from server:\n$responseData\n";

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
