<?php

namespace App\Services\Geographic\Service;

use App\Services\Geographic\Contract\WeatherServiceInterface;
use App\Services\Geographic\DTO\WeatherDataDTO;
use App\Services\Geographic\Scraper\GetWeatherHistoric;
use Illuminate\Support\Facades\Storage;
use DatetimeImmutable;

class WeatherService implements WeatherServiceInterface
{
    /**
     * @return WeatherDataDTO[]
     */
    public function getWeatherData(float $latitude, float $longitude, string $hash): array
    {
        if(Storage::disk('local')->exists("weather_data/weather_{$hash}.json")) {
            $jsonContent = Storage::disk('local')->get("weather_data/weather_{$hash}.json");
            $weatherDataArray = json_decode($jsonContent, true);
        } else {
            $scraper = new GetWeatherHistoric($latitude, $longitude);
            $weatherDataArray = $scraper();
            $this->createJsonFile($hash, $weatherDataArray);
        }

        // $weatherDataDTOs = [];
        // foreach ($weatherDataArray['daily']['time'] as $index => $time) {
        //     $weatherDataDTOs[] = new WeatherDataDTO(
        //         $weatherDataArray['daily']['temperature_2m_max'][$index],
        //         $weatherDataArray['daily']['temperature_2m_min'][$index],
        //         $weatherDataArray['daily']['temperature_2m_mean'][$index],
        //         $weatherDataArray['daily']['sunshine_duration'][$index],
        //         $weatherDataArray['daily']['weathercode'][$index],
        //         $weatherDataArray['daily']['precipitation_sum'][$index],
        //         new \DateTime($time)
        //     );
        // }

        $daily = $weatherDataArray['daily'];

        $indicators = [
            'degree_days_per_year' => $this->degreeDays($daily)/5,
            'frost_days_per_year' => $this->numberOfFrostDays($daily)/5,
            'late_frost_days_per_year' => $this->numberOfLateFrostDays($daily)/5,
            'last_frost_date' => $this->lastFrostDate($daily)?->format('Y-m-d'),
            'last_frost_date_per_year' => $this->lastFrostDatePerYear($daily),
            'hot_days_per_year' => $this->numberOfHotDays($daily)/5,
            'extreme_heat_days_per_year' => $this->numberOfExtremeHeatDays($daily)/5,
            'max_consecutive_extreme_heat_days' => $this->maxConsecutiveExtremeHeatDays($daily),
            'rain_mm_per_year' => $this->totalPrecipitation($daily)/5,
            'max_dry_days' => $this->maxConsecutiveDryDays($daily),
            'heavy_rain_days_per_year' => $this->numberOfHeavyRainDays($daily)/5,
            'sunshine_hours_per_year' => round($this->totalSunshineHours($daily)/5, 2),
            'thermal_amplitude' => round($this->meanThermalAmplitude($daily), 2),
            'unstable_ratio' => round($this->unstableWeatherRatio($daily), 2)
        ];

        //dump($indicators);

        $score = $this->climateScore($indicators);

        //dump($score);

        $comment = $this->climateScoreCommentary($score, $indicators);
        //dump($comment);

        $confidence = $this->climateConfidenceIndex($daily, $indicators);
        //dump($confidence);

        $confidenceComment = $this->climateConfidenceComment($confidence['index']);
        //dump($confidenceComment);

        $climateData = [
            'indicators' => $indicators,
            'globalScore' => $score['total'],
            'globalCommentary' => $this->globalClimateComment($score['total']),
            'commentaries' => $comment,
            'confidenceScore' => $confidence['index'],
            'globalConfidence' => $confidenceComment,
        ];

        return $climateData;
    }

    protected function createJsonFile(string $hash, array $data): void
    {
        Storage::disk('local')->put("weather_data/weather_{$hash}.json", json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * 
     * Functions about temperature statistics
     * 
     */

    public function meanAnnualTemperature(array $daily): float
    {
        return array_sum($daily['temperature_2m_mean']) / count($daily['temperature_2m_mean']);
    }

    public function numberOfFrostDays(array $daily): int
    {
        return count(array_filter(
            $daily['temperature_2m_min'],
            fn(float $t): bool => $t < 0.0
        ));
    }

    public function numberOfLateFrostDays(array $daily, string $afterDate = '03-15'): int 
    {
        $count = 0;

        foreach ($daily['time'] as $i => $date) {
            $day = new DateTimeImmutable($date);
            $threshold = new DateTimeImmutable($day->format('Y') . '-' . $afterDate);

            if ($day >= $threshold && $daily['temperature_2m_min'][$i] < 0.0) {
                $count++;
            }
        }

        return $count;
    }

    function lastFrostDate(array $daily): ?DateTimeImmutable
    {
        $last = null;

        foreach ($daily['time'] as $i => $date) {
            if ($daily['temperature_2m_min'][$i] < 0.0) {
                $last = new DateTimeImmutable($date);
            }
        }

        return $last;
    }

    public function lastFrostDatePerYear(array $daily): array
    {
        $lastFrostByYear = [];

        foreach ($daily['time'] as $i => $dateString) {
            $date = new DateTimeImmutable($dateString);
            $year = (int) $date->format('Y');

            if ($daily['temperature_2m_min'][$i] < 0.0) {
                if (
                    !isset($lastFrostByYear[$year]) ||
                    $date > $lastFrostByYear[$year]
                ) {
                    $lastFrostByYear[$year] = $date;
                }
            }
        }

        ksort($lastFrostByYear);

        return $lastFrostByYear;
    }

    public function analyzeLateFrosts(array $lastFrostByYear): array
    {
        $afterApril15 = 0;
        $afterMay1 = 0;
        $afterMay15 = 0;
        $latestDate = null;

        foreach ($lastFrostByYear as $year => $date) {
            $monthDay = (int) $date->format('md');

            if ($monthDay >= 415) {
                $afterApril15++;
            }

            if ($monthDay >= 501) {
                $afterMay1++;
            }

            if ($monthDay >= 515) {
                $afterMay15++;
            }

            if ($latestDate === null || $date > $latestDate) {
                $latestDate = $date;
            }
        }

        return [
            'years_with_frost_after_april_15' => $afterApril15,
            'years_with_frost_after_may_1' => $afterMay1,
            'years_with_frost_after_may_15' => $afterMay15,
            'latest_frost_date_overall' => $latestDate,
        ];
    }


    public function numberOfHotDays(array $daily, float $threshold = 30.0): int
    {
        return count(array_filter(
            $daily['temperature_2m_max'],
            fn(float $t): bool => $t >= $threshold
        ));
    }

    public function numberOfExtremeHeatDays(array $daily, float $threshold = 35.0): int
    {
        return count(array_filter(
            $daily['temperature_2m_max'],
            fn(float $t): bool => $t >= $threshold
        ));
    }

    public function maxConsecutiveExtremeHeatDays(array $daily, float $threshold = 35.0): int 
    {
        $max = 0;
        $current = 0;

        foreach ($daily['temperature_2m_max'] as $t) {
            if ($t >= $threshold) {
                $current++;
                $max = max($max, $current);
            } else {
                $current = 0;
            }
        }

        return $max;
    }


    public function meanThermalAmplitude(array $daily): float
    {
        $sum = 0.0;
        $count = count($daily['temperature_2m_max']);

        for ($i = 0; $i < $count; $i++) {
            $sum += $daily['temperature_2m_max'][$i] - $daily['temperature_2m_min'][$i];
        }

        return $sum / $count;
    }

    public function degreeDays(array $daily, float $baseTemp = 5.0): float
    {
        $sum = 0.0;

        foreach ($daily['temperature_2m_mean'] as $t) {
            $sum += max(0.0, $t - $baseTemp);
        }

        return $sum;
    }

    /**
     * 
     * Functions about precipitation statistics
     * 
     */

    public function totalPrecipitation(array $daily): float
    {
        return array_sum($daily['precipitation_sum']);
    }

    public function numberOfHeavyRainDays(array $daily, float $thresholdMm = 20.0): int
    {
        return count(array_filter(
            $daily['precipitation_sum'],
            fn(float $p): bool => $p >= $thresholdMm
        ));
    }

    public function maxConsecutiveDryDays(array $daily, float $thresholdMm = 1.0): int
    {
        $max = 0;
        $current = 0;

        foreach ($daily['precipitation_sum'] as $p) {
            if ($p < $thresholdMm) {
                $current++;
                $max = max($max, $current);
            } else {
                $current = 0;
            }
        }

        return $max;
    }

    /**
     * Functions about sunshine statistics
     */

    public function totalSunshineHours(array $daily): float
    {
        return array_sum($daily['sunshine_duration']) / 3600;
    }

    public function meanDailySunshineHours(array $daily): float
    {
        return totalSunshineHours($daily) / count($daily['sunshine_duration']);
    }

    /**
     * Functions about weather code statistics
     */

    public function weatherCodeFrequency(array $daily): array
    {
        $freq = [];

        foreach ($daily['weathercode'] as $code) {
            $freq[$code] ??= 0;
            $freq[$code]++;
        }

        arsort($freq);

        return $freq;
    }

    public function unstableWeatherRatio(array $daily): float
    {
        $unstableCodes = range(51, 99);

        $unstableDays = count(array_filter(
            $daily['weathercode'],
            fn(int $code): bool => in_array($code, $unstableCodes, true)
        ));

        return $unstableDays / count($daily['weathercode']);
    }

    /**
     * Scoring Generic Function
     */

    public function scoreByThresholds(float $value, array $thresholds): int 
    {
        foreach ($thresholds as $threshold => $score) {
            if ($value >= $threshold) {
                return $score;
            }
        }

        return (int) array_values($thresholds)[array_key_last($thresholds)];
    }

    /**
     * Scoring Temperature
     */

    public function scoreDegreeDays(float $degreeDaysPerYear): int
    {
        return $this->scoreByThresholds($degreeDaysPerYear, [
            2800 => 15,
            2400 => 12,
            2000 => 8,
            0    => 4,
        ]);
    }

    public function scoreFrostDays(float $frostDaysPerYear): int
    {
        return $this->scoreByThresholds(-$frostDaysPerYear, [
            -20 => 10,
            -40 => 7,
            -60 => 4,
            -999 => 1,
        ]);
    }

    public function scoreHotDays(float $hotDaysPerYear): int
    {
        return $this->scoreByThresholds(-$hotDaysPerYear, [
            -10 => 5,
            -20 => 3,
            -999 => 1,
        ]);
    }

    /** 
     * Scoring Precipitations
     */

    public function scoreAnnualRain(float $mmPerYear): int
    {
        if ($mmPerYear >= 700 && $mmPerYear <= 900) {
            return 10;
        }

        if (
            ($mmPerYear >= 500 && $mmPerYear < 700) ||
            ($mmPerYear > 900 && $mmPerYear <= 1100)
        ) {
            return 7;
        }

        return 4;
    }

    public function scoreMaxDryStreak(int $days): int
    {
        return $this->scoreByThresholds(-$days, [
            -14 => 15,
            -21 => 10,
            -30 => 6,
            -999 => 2,
        ]);
    }

    public function scoreHeavyRainDays(float $daysPerYear): int
    {
        return $this->scoreByThresholds(-$daysPerYear, [
            -5 => 5,
            -10 => 3,
            -999 => 1,
        ]);
    }

    /**
     * Scoring Sunshine
     */

    public function scoreSunshine(float $hoursPerYear): int
    {
        return $this->scoreByThresholds($hoursPerYear, [
            2200 => 25,
            1900 => 20,
            1600 => 15,
            0    => 8,
        ]);
    }

    /**
     * Scoring Weather Stability
     */

    public function scoreWeatherInstability(float $unstableRatio): int
    {
        $percent = $unstableRatio * 100;

        return $this->scoreByThresholds(-$percent, [
            -40 => 10,
            -55 => 7,
            -70 => 4,
            -999 => 1,
        ]);
    }

    public function scoreThermalAmplitude(float $amplitude): int
    {
        return $this->scoreByThresholds(-$amplitude, [
            -8 => 5,
            -10 => 3,
            -999 => 1,
        ]);
    }

    /**
     * Scoring Late Frosts
     */

    public function lateFrostRiskIndex(int $yearsWithLateFrost, int $totalYears): string 
    {
        $ratio = $yearsWithLateFrost / $totalYears;

        return match (true) {
            $ratio === 0.0 => 'absent',
            $ratio <= 0.2 => 'rare',
            $ratio <= 0.5 => 'occasionnel',
            $ratio <= 0.8 => 'fréquent',
            default => 'structurel',
        };
    }


    /**
     * Overall Weather Score
     */

    public function climateScore(array $indicators): array
    {
        $temperature =
            $this->scoreDegreeDays($indicators['degree_days_per_year']) +
            $this->scoreFrostDays($indicators['frost_days_per_year']) +
            $this->scoreHotDays($indicators['hot_days_per_year']);

        $water =
            $this->scoreAnnualRain($indicators['rain_mm_per_year']) +
            $this->scoreMaxDryStreak($indicators['max_dry_days']) +
            $this->scoreHeavyRainDays($indicators['heavy_rain_days_per_year']);

        $sunshine =
            $this->scoreSunshine($indicators['sunshine_hours_per_year']);

        $risks =
            $this->scoreWeatherInstability($indicators['unstable_ratio']) +
            $this->scoreThermalAmplitude($indicators['thermal_amplitude']);

        return [
            'total' => $temperature + $water + $sunshine + $risks,
            'breakdown' => [
                'temperature' => $temperature,
                'water' => $water,
                'sunshine' => $sunshine,
                'risks' => $risks,
            ],
        ];
    }

    /**
     * Qualification simple des sous-scores
     */

    public function qualifyScore(int $score, int $max): string
    {
        $ratio = $score / $max;

        return match (true) {
            $ratio >= 0.8 => 'très favorable',
            $ratio >= 0.6 => 'favorable',
            $ratio >= 0.4 => 'mitigé',
            default => 'limitant'
        };
    }

    public function extremeHeatQualification(int $days35, int $maxStreak): string
    {
        return match (true) {
            $days35 === 0 => 'absente',
            $days35 <= 5 && $maxStreak <= 2 => 'ponctuelle',
            $days35 <= 15 || $maxStreak <= 4 => 'significative',
            default => 'critique',
        };
    }

    public function lateFrostQualification(int $lateFrostDays, ?DateTimeImmutable $lastFrost): string 
    {
        if ($lateFrostDays === 0) {
            return 'absent';
        }

        $monthDay = (int) $lastFrost?->format('md');

        if ($monthDay >= 415) {
            return 'très tardif';
        }

        return $lateFrostDays <= 3 ? 'ponctuel' : 'fréquent';
    }

    public function heatDaysQualification(float $hotDaysPerYear): string
    {
        return match (true) {
            $hotDaysPerYear <= 10 => 'rares',
            $hotDaysPerYear <= 25 => 'modérées',
            $hotDaysPerYear <= 40 => 'fréquentes',
            $hotDaysPerYear <= 60 => 'élevées',
            default => 'très élevées',
        };
    }

     /**
     * Commentaire température
     */
    public function commentTemperature(int $score, float $degreeDays, float $frostDays, float $hotDays): string
    {
        $qualif = $this->qualifyScore($score, 30);
        $heatQualif = $this->heatDaysQualification($hotDays);

        return sprintf(
            "🌡️ Température : %s. Le cumul de chaleur est élevé (%.0f degrés-jours/an), " .
            "indiquant un fort potentiel de production. En revanche, les épisodes de forte chaleur sont %s " .
            "(%.0f jours ≥ 30 °C/an), ce qui peut générer du stress thermique et nécessiter des adaptations culturales. " .
            "Le gel reste présent (%.0f jours/an).",
            $qualif,
            $degreeDays,
            $heatQualif,
            $hotDays,
            $frostDays
        );
    }

    public function commentExtremeHeat(int $days35, int $maxStreak): string 
    {
        $qualif = $this->extremeHeatQualification($days35, $maxStreak);

        return match ($qualif) {
            'absente' =>
                "🔥 Chaleurs extrêmes : absentes. Aucun épisode ≥ 35 °C observé.",
            'ponctuelle' =>
                "🔥 Chaleurs extrêmes : ponctuelles ($days35 jours ≥ 35 °C). Risque limité.",
            'significative' =>
                "🔥 Chaleurs extrêmes : significatives ($days35 jours ≥ 35 °C, jusqu'à $maxStreak jours consécutifs). " .
                "Un stress thermique est probable pour certaines cultures.",
            default =>
                "🔥 Chaleurs extrêmes : critiques ($days35 jours ≥ 35 °C, jusqu'à $maxStreak jours consécutifs). " .
                "Risque élevé de pertes sans protections adaptées (ombrage, irrigation, choix variétal).",
        };
    }

    public function commentLateFrost(int $lateFrostDays, ?DateTimeImmutable $lastFrost): string 
    {
        if ($lateFrostDays === 0) {
            return "❄️ Gels tardifs : absents. Aucun gel observé après le redémarrage végétatif (fixé au 15 mars).";
        }

        return sprintf(
            "❄️ Gels tardifs : %s (%d jours après mi-mars). Dernier gel observé le %s. " .
            "Risque important pour les cultures précoces.",
            $this->lateFrostQualification($lateFrostDays, $lastFrost),
            $lateFrostDays,
            $lastFrost?->format('d/m') ?? '—'
        );
    }

    public function commentStructuralLateFrost(array $analysis, int $totalYears): string 
    {
        $risk = $this->lateFrostRiskIndex(
            $analysis['years_with_frost_after_april_15'],
            $totalYears
        );

        $latest = $analysis['latest_frost_date_overall']
            ? $analysis['latest_frost_date_overall']->format('d/m')
            : '—';

        return match ($risk) {
            'absent' =>
                "❄️ Aucun gel observé après le 15 avril sur la période étudiée.",
            'rare' =>
                "❄️ Gels tardifs rares (après le 15 avril dans {$analysis['years_with_frost_after_april_15']} année(s) sur $totalYears).",
            'occasionnel' =>
                "❄️ Gels tardifs occasionnels (après le 15 avril dans {$analysis['years_with_frost_after_april_15']} année(s) sur $totalYears). Risque à intégrer pour les cultures précoces.",
            'fréquent' =>
                "❄️ Gels tardifs fréquents (après le 15 avril dans {$analysis['years_with_frost_after_april_15']} années sur $totalYears). Implantation précoce risquée.",
            default =>
                "❄️ Gels tardifs structurels (après le 15 avril dans {$analysis['years_with_frost_after_april_15']} année(s) sur $totalYears). Risque élevé pour cultures précoces ou non protégées.",
        };
    }



    /** 
     * Commentaire eau 
     */

    public function commentWater(int $score, float $rain, int $dryStreak, float $heavyRainDays): string 
    {
        $qualif = $this->qualifyScore($score, 30);

        $irrigationNote = $dryStreak > 21
            ? "Des périodes sèches prolongées rendent l'irrigation nécessaire en saison."
            : "Les périodes sèches restent généralement gérables.";

        return sprintf(
            "🌧️ Eau : %s. La pluviométrie annuelle (%.0f mm/an) est bien répartie. " .
            "La sécheresse maximale atteint %d jours consécutifs. %s",
            $qualif,
            $rain,
            $dryStreak,
            $irrigationNote
        );
    }

    /**
     * Commentaire ensoleillement
     */

    public function commentSunshine(int $score, float $sunHours): string 
    {
        $qualif = $this->qualifyScore($score, 25);

        return sprintf(
            "☀️ Ensoleillement : %s. Avec environ %.0f heures par an, " .
            "le potentiel photosynthétique est excellent pour le maraîchage.",
            $qualif,
            $sunHours
        );
    }

    /**
     * Commentaire risques climatiques
     */

    public function commentRisks(int $score, float $unstableRatio, float $amplitude): string 
    {
        $qualif = $this->qualifyScore($score, 15);

        $instabilityPercent = round($unstableRatio * 100);

        return sprintf(
            "⚠️ Risques climatiques : %s. Le climat est marqué par une instabilité modérée (%d %% de jours perturbés). " .
            "L'amplitude thermique moyenne (%.1f °C) reste favorable à la stabilité des cultures.",
            $qualif,
            $instabilityPercent,
            $amplitude
        );
    }

    /**
     * Commentaire global
     */

    public function globalClimateComment(int $totalScore): string
    {
        return match (true) {
            $totalScore >= 85 =>
                "🌱 Climat très favorable au maraîchage biologique, avec peu de contraintes structurelles.",
            $totalScore >= 70 =>
                "🌱 Climat globalement favorable au maraîchage biologique, avec quelques points de vigilance à intégrer dans la conduite culturale.",
            $totalScore >= 55 =>
                "🌱 Climat moyennement favorable, nécessitant des adaptations techniques (choix variétal, irrigation, protection).",
            default =>
                "🌱 Climat contraignant pour le maraîchage, demandant une forte adaptation des pratiques.",
        };    
    }

    /**
     * Commentaire explicatif du score climatique
     */
    public function climateScoreCommentary(array $score, array $indicators): string
    {
        $lines = [];

        $lines[] = $this->commentTemperature(
            $score['breakdown']['temperature'],
            $indicators['degree_days_per_year'],
            $indicators['frost_days_per_year'],
            $indicators['hot_days_per_year']
        );

        $lines[] = $this->commentExtremeHeat(
            $indicators['extreme_heat_days_per_year'],
            $indicators['max_consecutive_extreme_heat_days']
        );

        //  $lines[] = $this->commentLateFrost(
        //     $indicators['late_frost_days_per_year'],
        //     $indicators['last_frost_date'] ? new DateTimeImmutable($indicators['last_frost_date']) : null
        // );

        $lines[] = $this->commentStructuralLateFrost(
            $this->analyzeLateFrosts($indicators['last_frost_date_per_year']),
            count($indicators['last_frost_date_per_year'])-1
        );

        $lines[] = $this->commentWater(
            $score['breakdown']['water'],
            $indicators['rain_mm_per_year'],
            $indicators['max_dry_days'],
            $indicators['heavy_rain_days_per_year']
        );

        $lines[] = $this->commentSunshine(
            $score['breakdown']['sunshine'],
            $indicators['sunshine_hours_per_year']
        );

        $lines[] = $this->commentRisks(
            $score['breakdown']['risks'],
            $indicators['unstable_ratio'],
            $indicators['thermal_amplitude']
        );

        return implode("\n\n", $lines);
    }

    /**
     * Math function utils
     */
    public function mean(array $values): float
    {
        return array_sum($values) / count($values);
    }

    public function standardDeviation(array $values): float
    {
        $mean = $this->mean($values);
        $variance = 0.0;

        foreach ($values as $v) {
            $variance += ($v - $mean) ** 2;
        }

        return sqrt($variance / count($values));
    }

    /**
     * Confidence thermal
     */
    public function confidenceThermal(array $daily): int
    {
        $std = $this->standardDeviation($daily['temperature_2m_mean']);

        return match (true) {
            $std <= 4.0 => 25,
            $std <= 5.0 => 18,
            $std <= 6.0 => 10,
            default     => 4,
        };
    }

    /**
     * Confidence rainfall
     */
    public function confidenceRain(array $daily): int
    {
        $std = $this->standardDeviation($daily['precipitation_sum']);

        return match (true) {
            $std <= 3.0 => 25,
            $std <= 5.0 => 15,
            default     => 6,
        };
    }

    /**
     * Confidence weather stbility
     */
    public function confidenceWeatherStability(float $unstableRatio): int
    {
        $percent = $unstableRatio * 100;

        return match (true) {
            $percent <= 40 => 25,
            $percent <= 55 => 18,
            $percent <= 70 => 10,
            default        => 4,
        };
    }

    /**
     * Penalities extreme events
     */
    public function confidenceExtremes(int $maxDryDays, float $fosinFrostDaysPerYear): int 
    {
        $score = 25;

        if ($maxDryDays >= 30) {
            $score -= 10;
        }

        if ($fosinFrostDaysPerYear >= 50) {
            $score -= 10;
        }

        return max(0, $score);
    }

    /**
     * Global confidence score
     */
    public function climateConfidenceIndex(array $daily, array $indicators): array 
    {
        $thermal = $this->confidenceThermal($daily);
        $rain = $this->confidenceRain($daily);
        $stability = $this->confidenceWeatherStability($indicators['unstable_ratio']);
        $extremes = $this->confidenceExtremes(
            $indicators['max_dry_days'],
            $indicators['frost_days_per_year']
        );

        $total = $thermal + $rain + $stability + $extremes;

        return [
            'index' => $total,
            'breakdown' => [
                'thermal' => $thermal,
                'rain' => $rain,
                'stability' => $stability,
                'extremes' => $extremes,
            ],
        ];
    }

    /**
     * Confidence commentary
     */
    public function climateConfidenceComment(int $index): string
    {
        return match (true) {
            $index >= 80 =>
                "Indice de confiance élevé : le climat est stable et prévisible d'une année sur l'autre.",
            $index >= 60 =>
                "Indice de confiance correct : le climat est globalement fiable mais avec des variations notables.",
            $index >= 40 =>
                "Indice de confiance moyen : la variabilité climatique nécessite une adaptation des pratiques.",
            default =>
                "Indice de confiance faible : forte variabilité interannuelle et risques climatiques marqués.",
        };
    }
}

