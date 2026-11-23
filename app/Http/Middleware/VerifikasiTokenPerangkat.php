<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifikasiTokenPerangkat
{
    public function handle(Request $request, Closure $next): Response
    {
        $tokenKonfigurasi = config('siram.device_token');
        $tokenPermintaan = $request->bearerToken();

        if (
            ! is_string($tokenKonfigurasi)
            || $tokenKonfigurasi === ''
            || ! is_string($tokenPermintaan)
            || ! hash_equals($tokenKonfigurasi, $tokenPermintaan)
        ) {
            return response()->json(['pesan' => 'Token tidak valid.'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
