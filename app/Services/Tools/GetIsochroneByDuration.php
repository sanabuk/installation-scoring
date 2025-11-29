<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Http;


class GetIsochroneByDuration
{
    protected float $lat;
    protected float $lon;
    protected int $duration; // in minutes
    protected string $type_locomotion;

    public function __construct(float $lat, float $lon, int $duration, string $type="driving-car")
    {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->duration = $duration*60;
        $this->type_locomotion = $type;
    }

    public function getIsochrone()
    {
        $url = 'https://api.openrouteservice.org/v2/isochrones/' . $this->type_locomotion;
        $response = Http::withHeaders([
            'Authorization' => env('OPEN_ROUTE_SERVICE_API_KEY')
        ])->post($url,[
            'locations' => [[$this->lon,$this->lat]],
            'range' => [$this->duration]
        ]);
        $isochroneData = json_decode($response, true);
        $polygon = $isochroneData['features'][0]['geometry']['coordinates'][0];
        return $polygon;
    }

    public function splitIsochrone(array $isochrone):array
    {
        $isochrone_length = count($isochrone);
        $isochrone_split_key = floor($isochrone_length/2);
        $first_polygon = $isochrone[0];

        $polygon_first_string = '';
        for ($i=0; $i < $isochrone_split_key; $i++) { 
            $polygon_first_string .= $isochrone[$i][1].' '.$isochrone[$i][0].' ';
        }
        $polygon_first_string .= $first_polygon[1].' '. $first_polygon[0];
        $polygon_first_string = rtrim($polygon_first_string);

        $polygon_second_string = '';
        for ($i=$isochrone_split_key; $i < $isochrone_length; $i++) { 
            $polygon_second_string .= $isochrone[$i][1].' '.$isochrone[$i][0].' ';
        }
        $polygon_second_string .= $isochrone[$isochrone_split_key][1].' '. $isochrone[$isochrone_split_key][0];

        return [$polygon_first_string, $polygon_second_string];
    }
}