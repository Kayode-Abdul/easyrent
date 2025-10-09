<?php

use Illuminate\Support\Facades\Route;

// Legacy: /regional -> redirect to /dashboard/regional
Route::redirect('/regional', '/dashboard/regional', 301);

// Legacy: /regional/dashboard -> canonical
Route::redirect('/regional/dashboard', '/dashboard/regional', 301);

// Legacy: /regional/properties -> new canonical
Route::redirect('/regional/properties', '/dashboard/regional/properties', 301);

// Legacy: /regional/marketers -> new canonical
Route::redirect('/regional/marketers', '/dashboard/regional/marketers', 301);

// Legacy: /regional/analytics -> new canonical
Route::redirect('/regional/analytics', '/dashboard/regional/analytics', 301);

// Legacy: /regional/pending-approvals -> new canonical
Route::redirect('/regional/pending-approvals', '/dashboard/regional/pending-approvals', 301);

// Legacy marketer properties path variations
Route::get('/regional/marketer/{id}/properties', function($id){
    return redirect()->route('regional.marketer.properties', ['id' => $id]);
});

// Approvals legacy endpoints
Route::post('/regional/property/{propId}/approve', function($propId){
    return redirect()->route('regional.property.approve', ['propId' => $propId]);
});
Route::post('/regional/property/{propId}/reject', function($propId){
    return redirect()->route('regional.property.reject', ['propId' => $propId]);
});
