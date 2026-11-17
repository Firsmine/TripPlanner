<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItineraryBlockController;
use App\Http\Controllers\ItineraryController;
use App\Http\Controllers\TemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // itinerary
    Route::post('itineraries', [ItineraryController::class, 'store']);
    Route::get('itineraries', [ItineraryController::class, 'index']);
    Route::get('itineraries/{slug}', [ItineraryController::class, 'show']);
    Route::put('itineraries/{slug}', [ItineraryController::class, 'update']);
    Route::delete('itineraries/{slug}', [ItineraryController::class, 'destroy']);

    // template
    Route::get('templates', [TemplateController::class, 'index']);
    Route::get('templates/{slug}', [TemplateController::class, 'show']);

    // itinerary blocks
    Route::post('itineraries/{slug}/blocks', [ItineraryBlockController::class, 'store']);
    // Route::put('itineraries/{slug}', [ItineraryBlockController::class, 'update']);
    // Route::put('itineraries/{slug}', [ItineraryBlockController::class, 'reorder']);
    // Route::delete('itineraries/{slug}', [ItineraryBlockController::class, 'destroy']);
});
