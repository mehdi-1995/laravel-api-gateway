<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GatewayController;

Route::any('/gateway/{path}', [GatewayController::class, 'proxy'])->where('path', '.*');
