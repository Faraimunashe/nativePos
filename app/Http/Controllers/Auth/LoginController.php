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

        $response = Http::post('http://127.0.0.1:8080/api/v1/login', [
            'email' => $request->email,
            'password' => $request->password
        ]);


        if ($response->successful()) {
            $data = $response->json();

            $user_data = $data['user'];

            Session::put('user', [
                'id' => $user_data['id'],
                'name' => $user_data['name'],
                'email' => $user_data['email'],
            ]);

            Session::put('api_token', $data['token']);

            return redirect()->intended('pos');
        }

        return back()->withErrors(['email' => 'The login details are not valid']);
    }


    public function logout()
    {
        $token = Session::get('api_token');

        if ($token) {
            $response = Http::withToken($token)->post('http://127.0.0.1:8080/api/v1/logout');

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
