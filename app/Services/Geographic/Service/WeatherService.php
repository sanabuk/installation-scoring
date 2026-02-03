<?php

namespace App\Services\Geographic\Service;

use App\Services\Geographic\Contract\WeatherServiceInterface;
use App\Services\Geographic\DTO\WeatherDataDTO;
use App\Services\Geographic\Scraper\GetWeatherHistoric;
use Illuminate\Support\Facades\Storage;

class WeatherService implements WeatherServiceInterface
{
    /**
     * @return WeatherDataDTO[]
     */
    public function getWeatherData(float $latitude, float $longitude): array
    {
        if(Storage::disk('local')->exists("weather_data/weather_{$latitude}_{$longitude}.json")) {
            $jsonContent = Storage::disk('local')->get("weather_data/weather_{$latitude}_{$longitude}.json");
            $weatherDataArray = json_decode($jsonContent, true);
        } else {
            $scraper = new GetWeatherHistoric($latitude, $longitude);
            $weatherDataArray = $scraper();
            $this->createJsonFile($latitude, $longitude, $weatherDataArray);
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
            'hot_days_per_year' => $this->numberOfHotDays($daily)/5,
            'rain_mm_per_year' => $this->totalPrecipitation($daily)/5,
            'max_dry_days' => $this->maxConsecutiveDryDays($daily),
            'heavy_rain_days_per_year' => $this->numberOfHeavyRainDays($daily)/5,
            'sunshine_hours_per_year' => $this->totalSunshineHours($daily)/5,
            'thermal_amplitude' => $this->meanThermalAmplitude($daily),
            'unstable_ratio' => $this->unstableWeatherRatio($daily)
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

    protected function createJsonFile(float $latitude, float $longitude, array $data): void
    {
        Storage::disk('local')->put("weather_data/weather_{$latitude}_{$longitude}.json", json_encode($data, JSON_PRETTY_PRINT));
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

    public function numberOfHotDays(array $daily, float $threshold = 30.0): int
    {
        return count(array_filter(
            $daily['temperature_2m_max'],
            fn(float $t): bool => $t >= $threshold
        ));
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

    /**
     * Commentaire température
     */
    public function commentTemperature(int $score, float $degreeDays, float $frostDays, float $hotDays): string
    {
        $qualif = $this->qualifyScore($score, 30);

        return sprintf(
            "🌡️ Température : %s. Le cumul de chaleur (%.0f degrés-jours/an) est adapté à une large gamme de cultures. " .
            "Le gel reste présent (%.0f jours/an) mais globalement maîtrisable, avec peu d'épisodes de forte chaleur (%.0f jours/an).",
            $qualif,
            $degreeDays,
            $frostDays,
            $hotDays
        );
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

