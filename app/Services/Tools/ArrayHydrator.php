<?php

namespace App\Services\Tools;

class ArrayHydrator
{
    /**
     * Hydrate un tableau (potentiellement multidimensionnel)
     * avec les données provenant d'un tableau source
     * en se basant sur une clé commune.
     */
    public function hydrate(array $assoc_array, array $json_array, string $compare_key_assoc, string $compare_key_json, string $target_key): array
    {
        // Pré-indexation des JSON par valeur de code_insee
        // Permet d'éviter de boucler inutilement plusieurs fois
        $indexed = [];

        foreach ($json_array as $obj) {
            $item = is_array($obj) ? $obj : $obj->jsonSerialize();
            if (!isset($item[$compare_key_json])) {
                continue;
            }

            $value = strtolower($item[$compare_key_json]);

            // Retire la clé de comparaison
            //unset($item[$compare_key_json]);

            $indexed[$value][] = $item;
        }
        // Maintenant, on fusionne pour chaque commune
        foreach ($assoc_array as &$city) {
            // Vérifie que la commune possède la clé de comparaison
            if (!isset($city[$compare_key_assoc])) {
                continue;
            }

            $value = strtolower($city[$compare_key_assoc]);

            // Initialiser le tableau cible si pas présent
            if (!isset($city[$target_key])) {
                $city[$target_key] = [];
            }

            // Si nous avons trouvé des éléments correspondants, on les ajoute
            if (isset($indexed[$value])) {
                $city[$target_key][] = $indexed[$value];
            }
        }

        return $assoc_array;
    }
}
