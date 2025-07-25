<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\HttpClientWithHeaderCapture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Services\EFTService;

class LoginController extends Controller
{

    public function index()
    {
        Session::forget(['api_token', 'user', 'terminal_state']);
        return inertia('Auth/LoginPage');
    }

    public function login(Request $request, HttpClientWithHeaderCapture $http)
    {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        $msg = "The login details are not valid";

        $url = base_url() . '/login';

        $response = $http->withHeaders(['X-LOCATION-AUTH' => get_token()])->post($url, [
            'email' => $request->email,
            'password' => $request->password
        ]);


        if ($response->successful()) {
            $data = $response->json();

            //dd($data);

            $user_data = $data['user'];

            Session::put('user', [
                'id' => $user_data['id'],
                'name' => $user_data['name'],
                'email' => $user_data['email'],
            ]);

            Session::put('api_token', $data['token']);

            Session::put('eft_codes', $data['rates']);

            if(initialize_on_startup())
            {
                $eft = EFTService::getInstance();
                $eft->initializeConnection();
            }

            return redirect()->intended('pos');
        }else{
            $data = $response->json();

            $msg = $data['message'];
        }

        return back()->withErrors(['email' => $msg]);
    }


    public function logout(HttpClientWithHeaderCapture $http)
    {
        $token = Session::get('api_token');
        $url = base_url() . '/logout';

        if ($token) {
            $response = $http->withToken($token)->post($url);

            if ($response->successful()) {
                Session::forget(['api_token', 'user', 'terminal_state']);
                return redirect()->route('login')->with('success', 'Logged out successfully');
            }

            return back()->withErrors('Logout failed. Please try again.');
        }

        Session::forget(['api_token', 'user', 'terminal_state']);
        return redirect()->route('login');
    }
}
