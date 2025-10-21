<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ZKTeco webhook endpoint for attendance data
Route::post('/zkteco/attendance', function (Request $request) {
    // Handle ZKTeco attendance data
    return response()->json(['status' => 'received']);
});
