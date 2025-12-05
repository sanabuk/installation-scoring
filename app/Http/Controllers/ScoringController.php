<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScoringController extends Controller
{
    public function startScoring(Request $request)
    {
        Log::info('Start scoring request received', ['data' => $request->all()]);
        return view('thankyou');
    }
}