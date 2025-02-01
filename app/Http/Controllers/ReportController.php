<?php

namespace App\Http\Controllers;

use Http;
use Illuminate\Http\Request;
use Session;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $currency = $request->currency;
        $type = $request->type;
        $reference = $request->reference;
        $token = Session::get('api_token');
        $sales = [];
        $query_params = [];
        $currencies = [];
        $types = [];
        $totals = [];

        if ($request->has('start_date') && !empty($start_date)) {
            $query_params['start_date'] = $start_date;
        }

        if ($request->has('end_date') && !empty($end_date)) {
            $query_params['end_date'] = $end_date;
        }

        if ($request->has('currency') && !empty($currency)) {
            $query_params['currency'] = $currency;
        }

        if ($request->has('type') && !empty($type)) {
            $query_params['type'] = $type;
        }

        if ($request->has('reference') && !empty($reference)) {
            $query_params['reference'] = $reference;
        }


        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->get('http://127.0.0.1:8080/api/v1/report', $query_params);

        if ($response->successful()) {
            $data = $response->json();

            $sales = $data['data']['sales'];
            $currencies = $data['data']['currencies'];
            $types = $data['data']['trans_types'];
            $totals = $data['data']['totals'];

        } else {
            return back()->withErrors(['error' => 'API Error: ' . $response->body()]);
        }

        //dd($sales);
        return inertia("ReportPage", [
            "sales" => $sales,
            "currencies"=> $currencies,
            "trans_types"=> $types,
            "totals" => $totals
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
