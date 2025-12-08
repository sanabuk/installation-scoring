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
}