<?php

namespace App\Services\Tools;

class ArrayHydrator
{
    /**
     * Hydrate un tableau (potentiellement multidimensionnel)
     * avec les données provenant d'un tableau source
     * en se basant sur une clé commune.
     */
    public function hydrate(array $data, array $source, string $key, array $exclude = []): array
    {
        $indexedSource = $this->indexSource($source, $key);
        return $this->hydrateRecursive($data, $indexedSource, $key, $exclude);
    }

    protected function indexSource(array $source, string $key): array
    {
        $indexed = [];

        foreach ($source as $item) {
            $itemArray = $item->jsonSerialize();
            if (isset($itemArray[$key])) {
                if(isset($indexed[$itemArray[$key]])) {
                    $indexed[$itemArray[$key]] = array_merge($indexed[$itemArray[$key]], $itemArray);
                } else {
                    $indexed[$itemArray[$key]] = $itemArray;
                }
            }
        }

        return $indexed;
    }


    protected function hydrateRecursive(array $data, array $indexedSource, string $key, array $exclude): array
    {
        foreach ($data as &$item) {
            if (is_array($item)) {
                if (isset($item[$key]) && isset($indexedSource[$item[$key]])) {
                    $item = $this->mergeItem($item, $indexedSource[$item[$key]], $exclude);
                }
                $item = $this->hydrateRecursive($item, $indexedSource, $key, $exclude);
            }
        }

        return $data;
    }

    protected function mergeItem(array $item, array $sourceData, array $exclude): array
    {
        foreach ($exclude as $ex) {
            unset($sourceData[$ex]);
        }

        return array_merge($item, $sourceData);
    }
}
