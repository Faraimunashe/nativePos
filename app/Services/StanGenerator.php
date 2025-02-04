<?php

namespace App\Services;

class StanGenerator
{
    private static ?StanGenerator $instance = null;
    private array $stanTable = [];

    private function __construct() {}

    public static function getInstance(): StanGenerator
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getNextStan(string $key): string
    {
        $stan = $this->stanTable[$key] ?? $this->generateInitialStan();
        $stan++;
        $this->stanTable[$key] = $stan;
        return str_pad((string)$stan, 6, '0', STR_PAD_LEFT);
    }

    public function getLastStan(string $key): ?string
    {
        return isset($this->stanTable[$key]) ? str_pad((string)$this->stanTable[$key], 6, '0', STR_PAD_LEFT) : null;
    }

    private function generateInitialStan(): int
    {
        return random_int(100000, 999999);
    }

    public function resetStan(string $key): void
    {
        unset($this->stanTable[$key]);
    }
}
