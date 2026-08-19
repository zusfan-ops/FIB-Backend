<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    if ($request->has('json') || ($request->expectsJson() && !$request->acceptsHtml())) {
        return response()->json([
            'app' => 'FIB UNDIP (桜言葉)',
            'version' => '1.0.0',
            'status' => 'online',
            'docs' => url('/api/v1'),
            'live_url' => 'https://fib.ordr.my.id',
        ]);
    }

    $landingPath = resource_path('views/landing.blade.php');
    if (file_exists($landingPath)) {
        return response(file_get_contents($landingPath), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    return view('landing');
});

// Halaman Publik Foto Dokumentasi yang di-share (bisa diakses siapa saja tanpa login)
Route::get('/p/{campusPhoto}', function (App\Models\CampusPhoto $campusPhoto) {
    $campusPhoto->load(['user:id,name,university', 'comments.user:id,name']);
    return view('public_photo', ['photo' => $campusPhoto]);
});

Route::get('/foto/{campusPhoto}', function (App\Models\CampusPhoto $campusPhoto) {
    $campusPhoto->load(['user:id,name,university', 'comments.user:id,name']);
    return view('public_photo', ['photo' => $campusPhoto]);
});

// Shortcut route untuk download APK versi rilis terbaru
Route::get('/download/apk', function () {
    return redirect('https://github.com/zusfan-ops/FIB-Backend/releases/latest');
});
Route::get('/download/latest', function () {
    return redirect('https://github.com/zusfan-ops/FIB-Backend/releases/latest');
});

// GitHub Webhook deploy route fallback
Route::match(['get', 'post'], '/deploy', [App\Http\Controllers\Api\DeployController::class, 'deploy']);

