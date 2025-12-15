<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScoringRequest;
use App\Jobs\ProcessScoring;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

class ScoringController extends Controller
{
    public function startScoring(ScoringRequest $request)
    {
        $validated_datas = $request->validated();
        Log::info('Start scoring request received', ['data' => $validated_datas]);
        ProcessScoring::dispatch($validated_datas['lon'], $validated_datas['lat'], $validated_datas['email']);
        return view('thankyou');
    }

    public function getScoringResult($code_insee)
    {
        $datas = json_decode(Storage::get('scoring_results_'.$code_insee.'_guillaume@test.fr.json'));
        // TODO A mettre à la fin du scoring. PDF mis à cet emplacement à but de configuration.
        //PDF::view('score', ['datas' => $datas,'code_insee' => $code_insee])->landscape()->disk('local')->save($code_insee.'.pdf');
        return view('score', ['datas' => $datas,'code_insee' => $code_insee]);
    }
}