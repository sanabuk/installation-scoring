<?php

namespace App\Services\Concurrence\Service;

use App\Services\Concurrence\DTO\AmapDTO;
use App\Services\Concurrence\Scraper\GetAmap;
use Illuminate\Support\Facades\Log;

class AmapService
{
    public function getAmap(string $departement_code, string $city_name)
    {
        try {
            $amapFromCsv = $this->createGetAmap($departement_code, $city_name);
            $raws_datas = $amapFromCsv();
            return $this->mapToAmapDTO($raws_datas);
        } catch (\Exception $e) {
            Log::error('Error in AmapService class: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function createGetAmap(string $departement_code, string $city_name): GetAmap
    {
        return new GetAmap($departement_code, $city_name);
    }

    protected function mapToAmapDTO($rawDatas)
    {
        try {
            $amap_list = [];
            foreach ($rawDatas as $rawData) {
                $amapDTO = new AmapDTO;
                $amapDTO->setName($rawData['Nom']);
                $amapDTO->setCity($rawData['Ville']);
                $amapDTO->setDepartment($rawData['Departement']);
                $amapDTO->setWebsites($rawData['Websites']);
                $amapDTO->setMail($rawData['Mail']);
                $amapDTO->setProducts($rawData['Produits']);
                $amapDTO->setInfos($rawData['Infos']);
                $amap_list[] = $amapDTO;
            }
            return $amap_list;
        } catch (\Exception $e) {
            Log::error('Error mapping to AmapDTO: ' . $e->getMessage());
            throw $e;
        }
    }
}