<?php

// custom made by Faraimunashe

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class HttpClientWithHeaderCapture
{
    protected PendingRequest $client;

    public function __construct()
    {
        $this->client = Http::timeout(10);
    }

    public function withToken(string $token): self
    {
        $this->client = $this->client->withToken($token);
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->client = $this->client->withHeaders($headers);
        return $this;
    }

    public function get(string $url, array $params = [])
    {
        $response = $this->client->get($url, $params);
        $this->captureHeaders($response);
        return $response;
    }

    public function post(string $url, array $data = [])
    {
        $response = $this->client->post($url, $data);
        $this->captureHeaders($response);
        return $response;
    }

    protected function captureHeaders($response)
    {
        $term_id = $response->header('X-TERMINAL-ID');
        $location_name = $response->header('X-LOCATION-NAME');
        $default_currency = $response->header('X-CURRENCY-CODE');
        $terminal_status = $response->header('X-TERMINAL-ACTIVE');
        $cash = $response->header('X-TRANSACT-CASH');
        $eft = $response->header('X-TRANSACT-EFT');
        $special = $response->header('X-TRANSACT-SPECIAL');

        if (empty($term_id) || empty($location_name) || empty($default_currency) || empty($terminal_status) || empty($cash) || empty($eft) || empty($special)) {
            return redirect()->route('login')->withErrors(['error' => 'An important terminal variable is missing contact admin']);
        }

        if ($terminal_status == false) {
            return redirect()->route('login')->withErrors(['error' => 'This terminal is not active at the moment']);
        }

        Session::put('terminal_state', [
            'term_id' => $term_id,
            'location_name' => $location_name,
            'default_currency' => $default_currency,
            'terminal_status' => filter_var($terminal_status, FILTER_VALIDATE_BOOLEAN),
            'cash' => filter_var($cash, FILTER_VALIDATE_BOOLEAN),
            'eft' => filter_var($eft, FILTER_VALIDATE_BOOLEAN),
            'special' => filter_var($special, FILTER_VALIDATE_BOOLEAN)
        ]);

    }
}
