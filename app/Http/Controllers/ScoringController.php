<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessScoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScoringController extends Controller
{
    public function startScoring(Request $request)
    {
        Log::info('Start scoring request received', ['data' => $request->all()]);
        ProcessScoring::dispatch($request->input('lon'), $request->input('lat'), $request->input('email'));
        return view('thankyou');
    }

    public function getScoringResult($code_insee)
    {
        return view('score', ['datas' => json_decode(Storage::get('scoring_results_41139_test@test.fr.json')),'code_insee' => $code_insee]);
    }
}