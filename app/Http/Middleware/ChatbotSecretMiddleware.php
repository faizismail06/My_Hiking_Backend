<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ChatbotSecretMiddleware
 *
 * Memvalidasi bahwa request berasal dari service chatbot My_Hiking_Python
 * melalui header X-Chatbot-Secret yang cocok dengan nilai CHATBOT_SECRET di .env.
 *
 * Konfigurasi di .env:
 *   CHATBOT_SECRET=your-secret-key-here
 *
 * Konfigurasi di My_Hiking_Python/config.py, tambahkan:
 *   CHATBOT_SECRET=your-secret-key-here
 * dan kirimkan sebagai header: X-Chatbot-Secret: <nilai>
 */
class ChatbotSecretMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('app.chatbot_secret');

        // Jika CHATBOT_SECRET belum dikonfigurasi, izinkan akses
        // (backward-compatible agar tidak langsung memblokir saat pertama deploy)
        if (empty($secret)) {
            return $next($request);
        }

        $provided = $request->header('X-Chatbot-Secret');

        if (!hash_equals($secret, (string) $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses tidak diizinkan. Header X-Chatbot-Secret tidak valid.',
            ], 403);
        }

        return $next($request);
    }
}
