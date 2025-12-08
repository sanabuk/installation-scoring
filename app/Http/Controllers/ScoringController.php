<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessScoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ScoringController extends Controller
{
    const INCOMING_TAX_SCORING_THRESHOLDS = [
        'TAXABLE_HOUSEHOLDS_PERCENT' => 45.3,
        'AVERAGE_SALARY_TAX' => 34.35,
        'AVERAGE_PENSION_TAX' => 26.14
    ];

    public function startScoring(Request $request)
    {
        Log::info('Start scoring request received', ['data' => $request->all()]);
        ProcessScoring::dispatch($request->input('lon'), $request->input('lat'), $request->input('email'));
        return view('thankyou');
    }

    public function getScoringResult($code_insee)
    {
        $datas = json_decode(Storage::get('scoring_results_37196_guillaume@test.fr.json'));
        foreach ($datas as $key => $data) {
            $scoringIncomingData = $this->scoringFromIncomingTax($data->incoming_tax[0][0]);
            $datas[$key]->scoring_incoming_tax = $scoringIncomingData;
        }
        return view('score', ['datas' => $datas,'code_insee' => $code_insee]);
    }

    private function scoringFromIncomingTax($incomingTaxDTO)
    {
        $percent_taxable_households = $incomingTaxDTO->number_of_taxed_households/$incomingTaxDTO->number_of_taxable_households * 100;
        $scoring_percent_taxable_households = ($percent_taxable_households / self::INCOMING_TAX_SCORING_THRESHOLDS['TAXABLE_HOUSEHOLDS_PERCENT']) * 100;
        $average_salary_tax = $incomingTaxDTO->amount_by_salary/$incomingTaxDTO->number_of_households_taxed_on_salary;
        $scoring_average_salary_tax = ($average_salary_tax / self::INCOMING_TAX_SCORING_THRESHOLDS['AVERAGE_SALARY_TAX']) * 100;
        $average_pension_tax = $incomingTaxDTO->amount_by_pension/$incomingTaxDTO->number_of_households_taxed_on_pension;
        $scoring_average_pension_tax = ($average_pension_tax / self::INCOMING_TAX_SCORING_THRESHOLDS['AVERAGE_PENSION_TAX']) * 100;
        $scoring_incoming_tax = round(($scoring_percent_taxable_households + $scoring_average_salary_tax + $scoring_average_pension_tax) / 3,2);
        return [
            'scoring_percent_taxable_households' => round($scoring_percent_taxable_households,2),
            'scoring_average_salary_tax' => round($scoring_average_salary_tax,2),
            'scoring_average_pension_tax' => round($scoring_average_pension_tax,2),
            'scoring_incoming_tax' => $scoring_incoming_tax
        ];
    }
}