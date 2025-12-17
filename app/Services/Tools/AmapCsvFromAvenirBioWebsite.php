<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class AmapCsvFromAvenirBioWebsite
{
    public function handler()
    {
        $path = 'amap.csv';
        $file = fopen(Storage::path($path), 'w');
        fputcsv($file,[
            'Nom',
            'Ville',
            'Departement',
            'Websites',
            'Mail',
            'Produits',
            'Infos'
        ], ';');
        $departements = [
            '01' => 'ain',
            '02' => 'aisne',
            '03' => 'allier',
            '04' => 'alpes-de-haute-provence',
            '05' => 'hautes-alpes',
            '06' => 'alpes-maritimes',
            '07' => 'ardeche',
            '08' => 'ardennes',
            '09' => 'ariege',
            '10' => 'aube',
            '11' => 'aude',
            '12' => 'aveyron',
            '13' => 'bouches-du-rhone',
            '14' => 'calvados',
            '15' => 'cantal',
            '16' => 'charente',
            '17' => 'charente-maritime',
            '18' => 'cher',
            '19' => 'correze',
            '2A' => 'corse-du-sud',
            '2B' => 'haute-corse',
            '21' => 'cote-d-or',
            '22' => 'cotes-d-armor',
            '23' => 'creuse',
            '24' => 'dordogne',
            '25' => 'doubs',
            '26' => 'drome',
            '27' => 'eure',
            '28' => 'eure-et-loir',
            '29' => 'finistere',
            '30' => 'gard',
            '31' => 'haute-garonne',
            '32' => 'gers',
            '33' => 'gironde',
            '34' => 'herault',
            '35' => 'ille-et-vilaine',
            '36' => 'indre',
            '37' => 'indre-et-loire',
            '38' => 'isere',
            '39' => 'jura',
            '40' => 'landes',
            '41' => 'loir-et-cher',
            '42' => 'loire',
            '43' => 'haute-loire',
            '44' => 'loire-atlantique',
            '45' => 'loiret',
            '46' => 'lot',
            '47' => 'lot-et-garonne',
            '48' => 'lozere',
            '49' => 'maine-et-loire',
            '50' => 'manche',
            '51' => 'marne',
            '52' => 'haute-marne',
            '53' => 'mayenne',
            '54' => 'meurthe-et-moselle',
            '55' => 'meuse',
            '56' => 'morbihan',
            '57' => 'moselle',
            '58' => 'nievre',
            '59' => 'nord',
            '60' => 'oise',
            '61' => 'orne',
            '62' => 'pas-de-calais',
            '63' => 'puy-de-dome',
            '64' => 'pyrenees-atlantiques',
            '65' => 'hautes-pyrenees',
            '66' => 'pyrenees-orientales',
            '67' => 'bas-rhin',
            '68' => 'haut-rhin',
            '69' => 'rhone',
            '70' => 'haute-saone',
            '71' => 'saone-et-loire',
            '72' => 'sarthe',
            '73' => 'savoie',
            '74' => 'haute-savoie',
            '75' => 'paris',
            '76' => 'seine-maritime',
            '77' => 'seine-et-marne',
            '78' => 'yvelines',
            '79' => 'deux-sevres',
            '80' => 'somme',
            '81' => 'tarn',
            '82' => 'tarn-et-garonne',
            '83' => 'var',
            '84' => 'vaucluse',
            '85' => 'vendee',
            '86' => 'vienne',
            '87' => 'haute-vienne',
            '88' => 'vosges',
            '89' => 'yonne',
            '90' => 'territoire-de-belfort',
            '91' => 'essonne',
            '92' => 'hauts-de-seine',
            '93' => 'seine-saint-denis',
            '94' => 'val-de-marne',
            '95' => 'val-d-oise',

            // DOM
            '971' => 'guadeloupe',
            '972' => 'martinique',
            '973' => 'guyane',
            '974' => 'la-reunion',
            '976' => 'mayotte',
        ];
        foreach ($departements as $code => $name) {
            $this->scrap($code, $name, $file);
        }
        return "Création csv des AMAP terminée.";  
    }
    public function scrap(string $department_code, string $department_name, $file)
    {
        $base_url = "https://avenir-bio.fr/amap,".$department_name.",".$department_code.".html";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'text/html; charset=utf-8'
            ])->get($base_url);

            $html = $response->body();
            $crawler = new Crawler($html);
            $nodes = $crawler->filter('.PTB20b');
            $nodes->each(function (Crawler $node) use ($department_code, $file) {
                $websites = $this->getWebsite($node);
                $offset = count($websites) ? 3 : 2;
                fputcsv($file,[
                    $this->getNameAndCity($node)[0],
                    strtolower($this->getNameAndCity($node)[1]),
                    $department_code,
                    count($websites) ? implode(' | ', $websites):'',
                    $this->getMail($node)??'',
                    $this->getProducts($node, $offset)??'',
                    $this->getInfos($node, $offset +1)??''
                ], ';');
            }); 
                     
        } catch (\Exception $e) {
            Log::error('Avenir Bio website scrap error in AmapCsvFromAvenirBioWebsite class: ' . $e->getMessage());
            //throw new \Exception('Avenir Bio website request failed with status: ' . $e->getMessage());
        }
    }

    public function getNameAndCity(Crawler $node): array
    {
        return explode(' à ', $node->filter('strong')->eq(0)->text());
    }

    public function getMail(Crawler $node): string
    {
        return explode(':',$node->filter('div')->eq(1)->filter('a')->eq(0)->attr('href'))[1]??'';
    }

    public function getWebsite(Crawler $node): array
    {
        $links_nodes = $node->filter('div')->eq(2)->filter('a')??'';
        $links = [];
        if($links_nodes === ''){
            return $links;
        }
        $links_nodes->each(function (Crawler $link_node, $i) use (&$links) {
            $links[] = $link_node->attr('href');
        });
        return $links;
    }

    public function getProducts(Crawler $node, int $offset): string
    {
        return explode(':',$node->filter('div')->eq($offset)->text())[1]??'';
    }

    public function getInfos(Crawler $node, int $offset): string
    {
        return $node->filter('div')->eq($offset)->text()??'';
    }
}