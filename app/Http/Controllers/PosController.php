<?php

namespace App\Http\Controllers;

use Http;
use Illuminate\Http\Request;
use Session;

class PosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search =  $request->search;
        $token = Session::get('api_token');
        $items = [];
        $currencies = [];

        $query_params = [];
        if ($request->has('search') && !empty($request->search)) {
            $query_params['search'] = $request->search;
        }

        $response = Http::withToken($token)->get('http://127.0.0.1:8080/api/v1/items', $query_params);

        if ($response->successful()) {
            $data = $response->json();
            $items = $data['data'];
            $currencies = $data['rates'];
        }

        return inertia("PosPage", [
            'items' => $items,
            'currencies' => $currencies
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
        //
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
