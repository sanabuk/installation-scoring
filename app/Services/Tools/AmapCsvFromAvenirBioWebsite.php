<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class AmapCsvFromAvenirBioWebsite
{
    public function scrap(string $department_code, string $department_name)
    {
        $base_url = "https://avenir-bio.fr/amap,".$department_name.",".$department_code.".html";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'text/html; charset=utf-8'
            ])->get($base_url);

            $html = $response->body();
            $crawler = new Crawler($html);
            $nodes = $crawler->filter('.PTB20b');
            dump('***************');
            $nodes->each(function (Crawler $node, $i) {
                dump($i);
                dump($this->getNameAndCity($node));
                dump($this->getMail($node));
                $websites = $this->getWebsite($node);
                dump($websites);
                $offset = count($websites) ? 3 : 2;
                dump($this->getProducts($node, $offset));
                dump($this->getInfos($node, $offset +1));
                dump('***************');
            });
            // dump($nodes->eq(4)->html());
            // dump(explode(':',$nodes->eq(0)->filter('div')->eq(3)->text())[1]);
            
        } catch (\Exception $e) {
            Log::error('Avenir Bio website scrap error in AmapCsvFromAvenirBioWebsite class: ' . $e->getMessage());
            throw new \Exception('Avenir Bio website request failed with status: ' . $e->getMessage());
        }
    }

    public function getNameAndCity(Crawler $node): array
    {
        return explode(' à ', $node->filter('strong')->eq(0)->text());
    }

    public function getMail(Crawler $node): string
    {
        return explode(':',$node->filter('div')->eq(1)->filter('a')->eq(0)->attr('href'))[1];
    }

    public function getWebsite(Crawler $node): array
    {
        $links_nodes = $node->filter('div')->eq(2)->filter('a');
        $links = [];
        $links_nodes->each(function (Crawler $link_node, $i) use (&$links) {
            $links[] = $link_node->attr('href');
        });
        return $links;
    }

    public function getProducts(Crawler $node, int $offset): string
    {
        return explode(':',$node->filter('div')->eq($offset)->text())[1];
    }

    public function getInfos(Crawler $node, int $offset): string
    {
        return $node->filter('div')->eq($offset)->text();
    }
}