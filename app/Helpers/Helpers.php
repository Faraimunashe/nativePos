<?php

use App\Models\Environment;

function base_url()
{
    $env = Environment::first();
    $ip = $env->server_ip;
    $ver = $env->server_version;
    $url = $ip . "/" . $ver;

    return $url;
}

function get_socket_ip()
{
    $env = Environment::first();
    $socket_ip = $env->socket_ip;
    return $socket_ip;
}


function get_socket_port()
{
    $env = Environment::first();
    $socket_port = $env->socket_port;
    return $socket_port;
}

function get_terminal_id()
{
    $env = Environment::first();
    $terminal = $env->terminal;
    return $terminal;
}

function get_location()
{
    $env = Environment::first();
    $location = $env->location;
    return $location;
}

function convert_minor($amount) {
    $amount = floatval($amount);
    return round($amount * 100);
}

function convert_major($amount) {
    $amount = intval($amount);
    return number_format($amount / 100, 2, '.', '');
}
