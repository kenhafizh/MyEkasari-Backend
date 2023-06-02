<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if(Auth::user() && Auth::user()->roles == 'ADMIN') //apkaah otentikasi user yang login dan user roles nya admin. jika iya maka lanjut request 
        {
            return $next($request);
        }
        
        return redirect('/'); //jika tidak maka dikembalikan halaman utama atau tidak boleh akses
    }
}
