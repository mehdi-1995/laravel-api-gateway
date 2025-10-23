<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GatewayController extends Controller
{
    public function proxy(Request $request, $path)
    {
        // لاگ کردن درخواست (Logging)
        Log::info('Gateway request', ['path' => $path, 'method' => $request->method()]);

        // Transformation: اضافه کردن header custom
        $headers = $request->headers->all();

        $cleanHeaders = [];
        foreach ($request->headers->all() as $k => $v) {
            $cleanHeaders[$k] = implode(', ', $v);
        }

        $headers['X-Gateway-Processed'] = 'true';

        $method = strtolower($request->method());
        $allowed = ['get', 'post', 'put', 'patch', 'delete', 'head', 'options'];

        if (!in_array($method, $allowed, true)) {
            abort(405, 'HTTP method not allowed');
        }

        // Forward به backend (Proxying)
        $backendUrl = "https://jsonplaceholder.typicode.com/{$path}";
        $client = Http::withHeaders($cleanHeaders)
            ->withoutVerifying(); // موقتاً برای تست local

        if (in_array($method, ['post', 'put', 'patch'])) {
            $client = $client->withBody(
                $request->getContent(),
                $request->header('Content-Type', 'application/json')
            );
        }

        try {
            $response = $client->$method($backendUrl, $request->query());

            Log::info('Backend response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->failed()) {
                Log::error('Backend request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'error' => 'Backend request failed',
                    'status' => $response->status(),
                ], $response->status());
            }

            $body = $response->json();
            if (is_array($body)) {
                $body['gateway_note'] = 'Processed by Laravel Gateway';
            } else {
                $body = ['gateway_note' => 'Processed by Laravel Gateway', 'data' => $body];
            }

            return response()->json($body, $response->status());
        } catch (\Throwable $e) {
            Log::error('Gateway error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Gateway Exception: ' . $e->getMessage()], 500);
        }
    }
}
