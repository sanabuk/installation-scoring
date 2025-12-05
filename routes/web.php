<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::post('/start-scoring', [App\Http\Controllers\ScoringController::class, 'startScoring'])->name('start.scoring');
