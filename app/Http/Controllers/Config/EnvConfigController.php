<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\Environment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Env;

class EnvConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $printers = connected_printers();

        return inertia("ConfigPage", [
            "config" => Configuration::first(),
            "printers" => $printers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'server_ip' => ['required', 'url'],
                'server_version' => ['required', 'string'],
                'socket_ip' => ['required', 'string'],
                'socket_port' => ['required', 'string'],
                'printer' => ['required', 'string'],
                'token' => ['required', 'string']
            ]);

            $env = Configuration::first();

            //dd($request->all());

            if ($env) {
                $env->server_ip = $request->server_ip;
                $env->server_version = $request->server_version;
                $env->socket_ip = $request->socket_ip;
                $env->socket_port = $request->socket_port;
                $env->printer = $request->printer;
                $env->token = $request->token;
                $env->save();

                return back()->with('success', 'Environment configuration updated successfully');
            } else {
                $env = new Configuration();
                $env->server_ip = $request->server_ip;
                $env->server_version = $request->server_version;
                $env->socket_ip = $request->socket_ip;
                $env->socket_port = $request->socket_port;
                $env->printer = $request->printer;
                $env->token = $request->token;
                $env->save();

                return back()->with('success', 'Environment configuration saved successfully');
            }


        } catch (Exception $e) {
            //dd("error");
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
