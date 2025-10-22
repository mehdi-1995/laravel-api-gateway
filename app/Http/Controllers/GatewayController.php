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
        $response = Http::withHeaders($cleanHeaders)
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->$method($backendUrl, $request->all());

        // Transformation روی response: مثلاً اضافه کردن field
        $body = $response->json();
        if (is_array($body)) {
            $body['gateway_note'] = 'Processed by Laravel Gateway';
        }

        return response()->json($body, $response->status());
    }
}
