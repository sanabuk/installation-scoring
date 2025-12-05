<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessScoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScoringController extends Controller
{
    public function startScoring(Request $request)
    {
        Log::info('Start scoring request received', ['data' => $request->all()]);
        ProcessScoring::dispatch($request->input('lon'), $request->input('lat'), $request->input('email'));
        return view('thankyou');
    }
}