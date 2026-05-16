<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

class CaptureApplicationContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            Context::add('user_id', $user->id);
            Context::add('user_name', $user->name);
            Context::add('user_role', $user->getRoleNames()->first());
            
            // Tambahkan konteks sekolah jika ada
            if (isset($user->school_id)) {
                Context::add('school_id', $user->school_id);
            }
        }

        Context::add('ip_address', $request->ip());
        Context::add('request_id', (string) \Illuminate\Support\Str::uuid());

        return $next($request);
    }
}
