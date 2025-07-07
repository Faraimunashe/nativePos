<?php

namespace App\Services;

use React\EventLoop\LoopInterface;
use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use SimpleXMLElement;
use Exception;

// Import the resolve helper function
use function React\Promise\resolve;

class EFTService1
{
    private static ?EFTService $instance = null;
    private LoopInterface $loop;
    private Connector $connector;
    private ?ConnectionInterface $connection = null;
    private ?Deferred $deferred = null;

    private string $buffer = '';
    private string $serverIp;
    private int $serverPort;
    private string $termId;
    private string $transCurrency = '';

    private function __construct(LoopInterface $loop)
    {
        $this->loop       = $loop;
        $this->connector  = new Connector($loop);
        $this->serverIp   = get_socket_ip();
        $this->serverPort = (int)get_socket_port();
        $this->termId     = get_terminal_id();
    }

    /**
     * Get the singleton instance, optionally providing a LoopInterface.
     */
    public static function getInstance(?LoopInterface $loop = null): EFTService
    {
        if (self::$instance === null) {
            $loop = $loop ?? Factory::create();
            self::$instance = new self($loop);
        }

        return self::$instance;
    }

    /**
     * Get the event loop (so you can call run()).
     */
    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * Initialize socket connection and send INIT XML.
     */
    public function initializeConnection(): PromiseInterface
    {
        return $this->connector
            ->connect("{$this->serverIp}:{$this->serverPort}")
            ->then(function (ConnectionInterface $conn) {
                $this->connection = $conn;
                $this->attachDataListener();
                return $this->writeFrame($this->generateXml('INIT'));
            });
    }

    /**
     * Send a transaction XML and return a promise resolving the parsed response.
     */
    public function sendXMLMessage(string $xml, string $transCurrency): PromiseInterface
    {
        $this->transCurrency = strtoupper($transCurrency);

        $ensureConn = $this->connection
            ? resolve(true)
            : $this->initializeConnection();

        return $ensureConn
            ->then(fn() => $this->writeFrame($xml))
            ->then(fn() => $this->waitForTransactionResponse())
            ->then(fn(string $response) => $this->parseTransactionResponse($response));
    }

    /**
     * Send CLOSE XML and optionally close the connection.
     */
    public function sendCloseXML(): PromiseInterface
    {
        return $this->writeFrame($this->generateXml('CLOSE'));
    }

    private function attachDataListener(): void
    {
        $this->connection->on('data', function (string $chunk) {
            $this->buffer .= $chunk;

            if ($this->deferred && strpos($this->buffer, '<Esp:Transaction') !== false) {
                if (preg_match(
                    '/<Esp:Interface.*?<Esp:Transaction.*?<\/Esp:Transaction>.*?<\/Esp:Interface>/s',
                    $this->buffer,
                    $matches
                )) {
                    $rawXml = $matches[0];
                    $this->buffer = '';
                    $this->deferred->resolve($rawXml);
                    $this->deferred = null;
                }
            }
        });
    }

    private function writeFrame(string $xml): PromiseInterface
    {
        $frame = $this->frameHeader(strlen($xml)) . $xml;
        $this->connection->write($frame);
        return resolve(true);
    }

    private function waitForTransactionResponse(): PromiseInterface
    {
        $this->deferred = new Deferred();
        // You can implement a timeout here if desired
        return $this->deferred->promise();
    }

    private function frameHeader(int $length): string
    {
        if ($length < 0xFFFF) {
            return pack('n', $length);
        }
        // 0xFFFF marker + 4-byte big-endian length
        return "\xFF\xFF" . pack('N', $length);
    }

    private function generateXml(string $action): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Esp:Interface xmlns:Esp="http://www.mosaicsoftware.com/Postilion/eSocket.POS/" Version="1.0">
  <Esp:Admin TerminalId="{$this->termId}" Action="{$action}">
    <Esp:Register Type="CALLBACK" EventId="IAN"/>
    <Esp:Register Type="CALLBACK" EventId="DATA_REQUIRED"/>
    <Esp:Register Type="EVENT" EventId="DEBUG_ALL"/>
  </Esp:Admin>
</Esp:Interface>
XML;
    }

    private function parseTransactionResponse(string $responseXml): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($responseXml, SimpleXMLElement::class, LIBXML_NOCDATA);
        if (!$xml) {
            return ['approved' => false, 'message' => 'Invalid XML', 'raw' => $responseXml];
        }

        $namespaces = $xml->getNamespaces(true);
        if (isset($namespaces['Esp'])) {
            $xml->registerXPathNamespace('Esp', $namespaces['Esp']);
        }

        $tx = $xml->xpath('//Esp:Transaction')[0] ?? null;
        if (!$tx) {
            return ['approved' => false, 'message' => 'No transaction node', 'raw' => $responseXml];
        }

        $actionCode    = (string) $tx['ActionCode'];
        $responseCode  = (string) $tx['ResponseCode'];
        $transactionId = (string) $tx['TransactionId'];

        return [
            'approved'       => ($actionCode === 'APPROVE' || $responseCode === '00'),
            'transaction_id' => $transactionId,
            'response_code'  => $responseCode,
            'action_code'    => $actionCode,
            'raw'            => $responseXml,
        ];
    }
}
