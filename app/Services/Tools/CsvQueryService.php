<?php

namespace App\Services\Tools;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;

class CsvQueryService
{
    protected string $path;
    protected array $filters = [];
    protected ?array $select = null;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Requête simple : where('col', 'value')
     */
    public function where(string $column, $value): self
    {
        $this->filters[] = [
            'type' => 'equals',
            'column' => $column,
            'value' => $value,
        ];

        return $this;
    }


    public function whereLike(string $column, string $pattern, bool $caseInsensitive = true): self
    {
        $this->filters[] = [
            'type' => 'like',
            'column' => $column,
            'pattern' => $pattern,
            'caseInsensitive' => $caseInsensitive,
        ];

        return $this;
    }


    /**
     * Sélectionner un sous-ensemble de colonnes
     */
    public function select(array $columns): self
    {
        $this->select = $columns;
        return $this;
    }

    /**
     * Retourne toutes les lignes (Lazy → pas de surcharge mémoire)
     */
    public function get(): LazyCollection
    {
        $fullPath = Storage::path($this->path);

        return LazyCollection::make(function () use ($fullPath) {
            $handle = fopen($fullPath, 'r');
            $header = fgetcsv($handle, separator: ';');
            while (($row = fgetcsv($handle, separator: ';')) !== false) {
                // Ignorer les lignes vides ou invalides
                if ($row === [null] || empty(array_filter($row))) {
                    continue;
                }

                // Vérifier que la ligne a le même nombre de colonnes que l'en-tête
                if (count($row) !== count($header)) {
                    // soit on ignore la ligne
                    continue;

                    // soit on peut logguer l'erreur :
                    // Log::warning('CSV: nombre de colonnes incorrect', ['row' => $row]);
                }
                $assoc = array_combine($header, $row);

                // Appliquer les filtres where()
                foreach ($this->filters as $filter) {
                    $column = $filter['column'];

                    if (!isset($assoc[$column])) {
                        continue 2;
                    }

                    $cellValue = (string) $assoc[$column];

                    switch ($filter['type']) {
                        case 'equals':
                            if ($cellValue != $filter['value']) {
                                continue 3;
                            }
                            break;

                        case 'like':
                            $cellValue = (string) $assoc[$column];
                            $pattern   = (string) $filter['pattern'];

                            if ($filter['caseInsensitive']) {
                                $cellValue = mb_strtolower($cellValue);
                                $pattern   = mb_strtolower($pattern);
                            }

                            // Échapper le pattern pour regex
                            $regex = preg_quote($pattern, '/');

                            // Traduction SQL LIKE → regex
                            $regex = str_replace('%', '.*', $regex);

                            // Match complet
                            if (!preg_match('/^' . $regex . '$/u', $cellValue)) {
                                continue 3; // ⬅️ ligne rejetée
                            }

                            break;
                    }
                }

                // Appliquer select()
                if ($this->select !== null) {
                    $assoc = array_intersect_key($assoc, array_flip($this->select));
                }

                yield $assoc;
            }

            fclose($handle);
        });
    }

    /**
     * Raccourci : tout récupérer
     */
    public function all(): LazyCollection
    {
        return $this->get();
    }

    /**
     * Raccourci : première ligne
     */
    public function first(): ?array
    {
        return $this->get()->first();
    }
}
