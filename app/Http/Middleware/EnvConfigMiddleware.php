<?php

namespace App\Http\Middleware;

use App\Models\Environment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnvConfigMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $config = Environment::first();

        if (is_null($config)) {
            return redirect()->route('configs')->withErrors(['error' => 'Please configure your environment first.']);
        }
        return $next($request);
    }
}
