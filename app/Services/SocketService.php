<?php

namespace App\Services;
use App\Models\Environment;

class SocketService
{
    private static ?SocketService $instance = null;
    private $socket;
    private $serverIp = Environment::first()->socket_ip;
    private $serverPort = Environment::first()->socket_ip;
    private $isConnected = false;

    private function __construct()
    {
        $this->initializeConnection();
    }

    public static function getInstance(): SocketService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeConnection()
    {
        $this->socket = @stream_socket_client("tcp://{$this->serverIp}:{$this->serverPort}", $errno, $errstr, 5);

        if ($this->socket) {
            stream_set_timeout($this->socket, 5);
            $this->isConnected = true;
        } else {
            $this->isConnected = false;
        }
    }

    public function isConnected(): bool
    {
        return $this->isConnected && !feof($this->socket);
    }

    public function reconnect()
    {
        $this->initializeConnection();
    }

    public function sendMessage($message)
    {
        if (!$this->isConnected()) {
            $this->reconnect();
        }

        if ($this->isConnected()) {
            fwrite($this->socket, $message);
            return fread($this->socket, 2048);
        } else {
            return "Socket connection failed.";
        }
    }
}
