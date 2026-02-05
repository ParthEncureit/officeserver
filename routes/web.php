<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'env' => config('app.env'),
    ]);
});

Route::get('/db-test', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'DB CONNECTED']);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'DB ERROR',
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::get('/users', function () {
    return DB::table('users')->select('id','name','email')->get();
});
