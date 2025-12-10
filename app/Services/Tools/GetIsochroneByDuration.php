<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetIsochroneByDuration
{
    protected float $lat;
    protected float $lon;
    protected int $duration; // in minutes
    protected string $type_locomotion;
    protected int $interval;

    public function __construct(float $lat, float $lon, int $duration=15, string $type="driving-car", int $interval=300)
    {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->duration = $duration*60;
        $this->type_locomotion = $type;
        $this->interval = $interval;
    }

    public function getIsochrone(): array
    {
        try {
            $url = 'https://api.openrouteservice.org/v2/isochrones/' . $this->type_locomotion;
            $response = Http::withHeaders([
                'Authorization' => env('OPEN_ROUTE_SERVICE_API_KEY')
            ])->post($url,[
                'locations' => [[$this->lon,$this->lat]],
                'range' => [$this->duration],
                'interval' => $this->interval
            ]);
            $isochroneData = json_decode($response, true);
            $polygons = $isochroneData['features'];
            return $polygons;
        } catch (\Exception $e) {
            Log::error('Error in GetIsochroneByDuration class: ' . $e->getMessage());
            throw $e;
        }
        
    }

    public function splitIsochrone(array $isochrone):array
    {
        $isochrone_length = count($isochrone);
        $isochrone_split_key = ceil($isochrone_length/3);
        $first_polygon = $isochrone[0];

        $polygon_first_string = '';
        for ($i=0; $i < $isochrone_split_key; $i++) { 
            $polygon_first_string .= $isochrone[$i][1].' '.$isochrone[$i][0].' ';
        }
        $polygon_first_string .= $first_polygon[1].' '. $first_polygon[0];
        $polygon_first_string = rtrim($polygon_first_string);

        $polygon_second_string = '';
        for ($i=$isochrone_split_key; $i < $isochrone_split_key * 2; $i++) { 
            $polygon_second_string .= $isochrone[$i][1].' '.$isochrone[$i][0].' ';
        }
        $polygon_second_string .= $isochrone[$isochrone_split_key][1].' '. $isochrone[$isochrone_split_key][0];
        $polygon_second_string = rtrim($polygon_second_string);

        $polygon_third_string = '';
        for ($i=$isochrone_split_key * 2; $i < $isochrone_length; $i++) { 
            $polygon_third_string .= $isochrone[$i][1].' '.$isochrone[$i][0].' ';
        }
        $polygon_third_string .= $isochrone[$isochrone_split_key * 2][1].' '. $isochrone[$isochrone_split_key * 2][0];
        $polygon_third_string = rtrim($polygon_third_string);

        $polygon_fourth_string = '';
        $polygon_fourth_string .= $isochrone[0][1].' '. $isochrone[0][0];
        $polygon_fourth_string .= $isochrone[$isochrone_split_key][1].' '. $isochrone[$isochrone_split_key][0];
        $polygon_fourth_string .= $isochrone[$isochrone_split_key * 2][1].' '. $isochrone[$isochrone_split_key * 2][0];
        $polygon_fourth_string .= $isochrone[0][1].' '. $isochrone[0][0];
        $polygon_fourth_string = rtrim($polygon_fourth_string);

        return [$polygon_first_string, $polygon_second_string, $polygon_third_string, $polygon_fourth_string];
    }

    public function splitIsochroneInto4(array $rings): array
    {
        // Bounding box
        $xs = array_column($rings, 0);
        $ys = array_column($rings, 1);

        $minX = min($xs);
        $maxX = max($xs);
        $minY = min($ys);
        $maxY = max($ys);

        $midX = ($minX + $maxX) / 2;
        $midY = ($minY + $maxY) / 2;

        // Définir les 4 rectangles (xmin, ymin, xmax, ymax)
        $rects = [
            [$minX, $minY, $midX, $midY], // bas-gauche
            [$midX, $minY, $maxX, $midY], // bas-droite
            [$minX, $midY, $midX, $maxY], // haut-gauche
            [$midX, $midY, $maxX, $maxY], // haut-droite
        ];

        $polygons = [];

        foreach ($rects as $rect) {
            [$rx1, $ry1, $rx2, $ry2] = $rect;

            // Récupérer les points de l'anneau qui sont dans le rectangle
            $clipped = array_values(array_filter($rings, function ($p) use ($rx1, $ry1, $rx2, $ry2) {
                return $p[0] >= $rx1 && $p[0] <= $rx2 && $p[1] >= $ry1 && $p[1] <= $ry2;
            }));

            // On va construire un polygone partant du centre (midX, midY)
            $center = [$midX, $midY];

            if (!count($clipped)) {
                // Aucun point de l'isochrone dans le rectangle -> on renvoie le rectangle lui-même
                $polyPts = [
                    [$rx1, $ry1],
                    [$rx2, $ry1],
                    [$rx2, $ry2],
                    [$rx1, $ry2],
                    [$rx1, $ry1],
                ];
            } else {
                // On ajoute le centre + les points triés par angle autour du centre
                $pts = $clipped;

                // s'assurer qu'on ne duplique pas le centre s'il est déjà présent
                $hasCenter = false;
                foreach ($pts as $pt) {
                    if ($pt[0] == $center[0] && $pt[1] == $center[1]) { $hasCenter = true; break; }
                }

                // Calculer l'angle de chaque point autour du centre pour trier
                usort($pts, function($a, $b) use ($center) {
                    $angA = atan2($a[1] - $center[1], $a[0] - $center[0]);
                    $angB = atan2($b[1] - $center[1], $b[0] - $center[0]);
                    if ($angA == $angB) return 0;
                    return ($angA < $angB) ? -1 : 1;
                });

                // Construire le polygone en commençant par le centre, puis les points triés, puis retomber sur le centre
                $polyPts = [];
                $polyPts[] = $center;
                foreach ($pts as $p) $polyPts[] = $p;
                $polyPts[] = $center; // fermer sur le centre (évite WKT invalide si nécessaire)
            }

            // Convertir en string WKT (POLYGON((x y, ...)))
            $wktRing = implode(' ', array_map(fn($pt) => $pt[1] . ' ' . $pt[0], $polyPts));
            $polygons[] = $wktRing;
        }
        Log::info('Split isochrone into 4 polygons.');
        return $polygons;
    }


}