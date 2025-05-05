<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{

    public function index()
    {
        return inertia('Auth/LoginPage');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
            'password' => ['required'],
        ]);

        $url = base_url() . '/login';

        $response = Http::post($url, [
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

            return redirect()->intended('pos');
        }

        return back()->withErrors(['email' => 'The login details are not valid']);
    }


    public function logout()
    {
        $token = Session::get('api_token');
        $url = base_url() . '/logout';

        if ($token) {
            $response = Http::withToken($token)->post($url);

            if ($response->successful()) {
                Session::forget(['api_token', 'user']);
                return redirect()->route('login')->with('success', 'Logged out successfully');
            }

            return back()->withErrors('Logout failed. Please try again.');
        }

        Session::forget(['api_token', 'user']);
        return redirect()->route('login');
    }
}
