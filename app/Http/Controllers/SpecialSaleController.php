<?php

namespace App\Http\Controllers;

use App\Services\PrintReceipt;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Session;

class SpecialSaleController extends Controller
{
    protected $receipt;

    public function __construct(PrintReceipt $receipt)
    {
        $this->receipt = $receipt;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            $validated_data = $request->validate([
                "amount" => ["required","numeric", 'min:0'],
                "type" => ["required","string","in:SPECIAL"],
                "currency" => ["required","string","max:3", "min:3"],
                'items' => ['required', 'array', 'min:1'],
                'items.*.item_id' => ['required', 'integer'],
                'items.*.qty' => ['required', 'integer', 'min:1'],
                'items.*.price' => ['required', 'numeric', 'min:0'],
                'items.*.total_price' => ['required', 'numeric', 'min:0'],
                'consumer_code' => ['required', 'string'],
                'terminal' => ['required', 'string'],
                'location' => ['required', 'string'],
            ]);

            $token = Session::get('api_token');
            $url = base_url() . '/special';

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->post($url, $validated_data);

            if ($response->successful()) {
                $data = $response->json();

                //dd($data);

                $sale = $data['sale'];

                $cashier_name = Session::get('user')['name'];
                //dd($sale['created_at']);
                $datetime = Carbon::parse($sale['created_at'])->setTimezone('Africa/Harare')->toDateTimeString();


                $receipt = $this->receipt->printReceipt($sale['reference'], $cashier_name, "CASH", $datetime, $request->items, $sale['currency'], $sale['amount'], $data['cash'], $data['change']);

                //dd($receipt);
                return back()->with([
                    'success' => 'Special sale recorded successfully'
                ]);
            } else {
                $error = json_decode($response->body(), true);
                return back()->withErrors(['error' => 'API Error: ' . $error['message']]);
            }
        }catch (Exception $e){
            return back()->withErrors(['error'=> $e->getMessage()]);
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
