<?php

use App\Models\Configuration;

function base_url()
{
    $env = Configuration::first();
    $ip = $env->server_ip;
    $ver = $env->server_version;
    $url = $ip . "/" . $ver;

    return $url;
}

function get_socket_ip()
{
    $env = Configuration::first();
    $socket_ip = $env->socket_ip;
    return $socket_ip;
}


function get_socket_port()
{
    $env = Configuration::first();
    $socket_port = $env->socket_port;
    return $socket_port;
}

function get_token()
{
    $env = Configuration::first();
    $token = $env->token;
    return $token;
}

function get_terminal_id()
{
    $state = session('terminal_state');

    return $state['term_id'];
}

function get_location()
{
    $state = session('terminal_state');

    return $state['location_name'];
}

function initialize_on_startup()
{
    $state = session('terminal_state');
    return $state['eft'];
}

function get_selected_printer()
{
    $env = Configuration::first();
    $printer = $env->printer;
    return $printer;
}

function convert_minor($amount) {
    $amount = floatval($amount);
    return round($amount * 100);
}

function convert_major($amount) {
    $amount = intval($amount);
    return number_format($amount / 100, 2, '.', '');
}

function connected_printers(){
    exec('wmic printer list brief', $output);

    $printer_names = [];

    foreach ($output as $line) {
        if (preg_match('/^\s*(\S.*\S)\s+\d+/', $line, $matches)) {
            $name = preg_replace('/\s+\d+$/', '', $matches[1]);
            $printer_names[] = trim($name);
        }
    }

    return $printer_names;
}

function get_eft_code($currencyCode)
{
    $rates = session('eft_codes', []);

    foreach ($rates as $rate) {
        if ($rate['code'] === $currencyCode) {
            return $rate['eft_code'];
        }
    }

    return null;
}

