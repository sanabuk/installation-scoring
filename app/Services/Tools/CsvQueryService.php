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
        $this->filters[] = compact('column', 'value');
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
                    if (!isset($assoc[$filter['column']]) || $assoc[$filter['column']] != $filter['value']) {
                        continue 2; // ignorer la ligne
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
