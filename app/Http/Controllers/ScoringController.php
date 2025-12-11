<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessScoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

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
        $datas = json_decode(Storage::get('scoring_results_'.$code_insee.'_guillaume@test.fr.json'));
        foreach ($datas as $key => $data) {
            $scoringIncomingData = $this->scoringFromIncomingTax($data->incoming_tax[0][0]);
            $datas[$key]->scoring_incoming_tax = $scoringIncomingData;
        }
        // TODO A mettre à la fin du scoring. PDF mis à cet emplacement à but de configuration.
        PDF::view('score', ['datas' => $datas,'code_insee' => $code_insee])->landscape()->disk('local')->save($code_insee.'.pdf');
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
            'scoring_percent_taxable_households_color' => $this->colorFromScore(round($scoring_percent_taxable_households,2)),
            'scoring_average_salary_tax' => round($scoring_average_salary_tax,2),
            'scoring_average_salary_tax_color' => $this->colorFromScore(round($scoring_average_salary_tax,2)),
            'scoring_average_pension_tax' => round($scoring_average_pension_tax,2),
            'scoring_average_pension_tax_color' => $this->colorFromScore(round($scoring_average_pension_tax,2)),
            'scoring_incoming_tax' => $scoring_incoming_tax,
            'scoring_incoming_tax_color' => $this->colorFromScore($scoring_incoming_tax)
        ];
    }

    private function colorFromScore(float $score): string
    {
        $score = max(0, min(150, $score));

        // Points clés
        $red     = [255, 0,   0];
        $orange  = [255, 145, 0];
        $light_green = [0, 255, 0];
        $green   = [65,   173, 84];

        if ($score <= 75) {
            // Dégradé Rouge → Orange
            $ratio = ($score - 100) / 100;

            $r = (int)($red[0]   + ($orange[0] - $red[0])   * $ratio);
            $g = (int)($red[1]   + ($orange[1] - $red[1])   * $ratio);
            $b = (int)($red[2]   + ($orange[2] - $red[2])   * $ratio);

        } 
        if ($score <= 100) {
            // Dégradé Orange → Vert Clair
            $ratio = ($score - 75) / 75;

            $r = (int)($orange[0] + ($light_green[0] - $orange[0]) * $ratio);
            $g = (int)($orange[1] + ($light_green[1] - $orange[1]) * $ratio);
            $b = (int)($orange[2] + ($light_green[2] - $orange[2]) * $ratio);

        }else {
            // Dégradé Vert Clair → Vert
            $ratio = ($score - 50) / 75;

            $r = (int)($light_green[0] + ($green[0] - $light_green[0]) * $ratio);
            $g = (int)($light_green[1] + ($green[1] - $light_green[1]) * $ratio);
            $b = (int)($light_green[2] + ($green[2] - $light_green[2]) * $ratio);
        }

        return "rgb($r, $g, $b)";
    }
}