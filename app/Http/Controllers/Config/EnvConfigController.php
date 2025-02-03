<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\Environment;
use Illuminate\Http\Request;
use Illuminate\Support\Env;

class EnvConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return inertia("ConfigPage", [
            "config" => Environment::first()
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
        try{
            $request->validate([
                'server_ip' => ['required','url'],
                'server_version' => ['required','string'],
                'default_currency' => ['required','string'],
                'enabled_payments'=> ['required','string'],
                'socket_ip'=> ['required','string'],
                'socket_port'=> ['required','string'],
                'location'=> ['required','string'],
                'terminal'=> ['required','string'],
            ]);

            $env = new Environment();
            $env->server_ip = $request->server_ip;
            $env->server_version = $request->server_version;
            $env->default_currency = $request->default_currency;
            $env->enabled_payments = $request->enabled_payments;
            $env->socket_ip = $request->socket_ip;
            $env->socket_port = $request->socket_port;
            $env->location = $request->location;
            $env->terminal = $request->terminal;
            $env->save();

            return back()->with(['success' => 'Environment configurations saved successfully']);
        }catch(\Exception $e){
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
