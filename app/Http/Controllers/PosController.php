<?php

namespace App\Http\Controllers;

use App\Services\EFTService;
use App\Services\HttpClientWithHeaderCapture;
use App\Services\PrintReceipt;
use Carbon\Carbon;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Session;

class PosController extends Controller
{
    protected $receipt;

    // Inject the ReceiptService into the controller
    public function __construct(PrintReceipt $receipt)
    {
        $this->receipt = $receipt;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, HttpClientWithHeaderCapture $http)
    {
        if(initialize_on_startup())
        {
            $eft = EFTService::getInstance();
            $eft->initializeConnection();
        }

        $token = Session::get('api_token');
        $items = [];
        $currencies = [];

        $query_params = [];
        if ($request->has('search') && !empty($request->search)) {
            $query_params['search'] = $request->search;
        }

        $url = base_url() . '/items';
        $response = $http->withToken($token)->withHeaders(['X-LOCATION-AUTH' => get_token()])->get($url, $query_params);


        if ($response->successful()) {
            $data = $response->json();
            //dd($data);
            $items = $data['data'];
            $currencies = $data['rates'];
        }

        return inertia("PosPage", [
            'items' => $items,
            'currencies' => $currencies,
            //'terminal' => get_terminal_id(),
            //'location' => get_location()
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
    public function store(Request $request, HttpClientWithHeaderCapture $http)
    {
        try{
            //sleep(10);
            $validated_data = $request->validate([
                "amount" => ["required","numeric", 'min:0'],
                "type" => ["required","string","in:CASH,EFT"],
                "currency" => ["required","string","max:3", "min:3"],
                'items' => ['required', 'array', 'min:1'],
                'items.*.item_id' => ['required', 'integer'],
                'items.*.qty' => ['required', 'integer', 'min:1'],
                'items.*.price' => ['required', 'numeric', 'min:0'],
                'items.*.total_price' => ['required', 'numeric', 'min:0'],
                'change' => ['required', 'numeric', 'min:0'],
                'cash' => ['required', 'numeric', 'min:0'],
            ]);

            $token = Session::get('api_token');
            $url = base_url() . '/sales';

            //dd($validated_data);

            $response = $http->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'X-LOCATION-AUTH' => get_token(),
            ])->post($url, $validated_data);

            if ($response->successful()) {
                $data = $response->json();
                $sale = $data['sale'];

                $cashier_name = Session::get('user')['name'];
                //dd($sale['created_at']);
                $datetime = Carbon::parse($sale['created_at'])->setTimezone('Africa/Harare')->toDateTimeString();


                $receipt = $this->receipt->printReceipt($sale['reference'], $cashier_name, "CASH", $datetime, $request->items, $request->currency, $sale['amount'], $data['cash'], $data['change']);

                //dd($receipt);
                return back()->with([
                    'success' => 'Sale recorded successfully'
                ]);
            } else {
                return back()->withErrors(['error' => 'API Error: ' . $response->body()]);
            }

        }catch (\Exception $e){
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
