<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiQueryTrait
{
    public function applyQueryFilters(Builder $query, Request $request): LengthAwarePaginator
    {
        // Filtrage
        if ($type = $request->query('type')) {
            $query->where('type_compte', $type);
        }
        if ($statut = $request->query('statut')) {
            $query->where('statut_compte', $statut);
        }
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('titulaire_compte', 'like', "%$search%")
                  ->orWhere('numero_compte', 'like', "%$search%");
            });
        }

        // Filtrer par archived (Postgres: boolean).
        // Accepter true/false, 1/0, "true"/"false" etc. et caster proprement.
            if ($request->has('archived')) {
                $archivedRaw = $request->query('archived');
                // FILTER_NULL_ON_FAILURE returns null if the value is not recognizable
                // (ex: 'foo'), avoiding adding an invalid where clause.
                $archived = filter_var($archivedRaw, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE);
                if (!is_null($archived)) {
                    // Use SQL boolean literal to ensure Postgres compares boolean to boolean
                    $query->whereRaw('archived = ' . ($archived ? 'true' : 'false'));
                }
        }

        // Tri
        $sort = $request->query('sort', 'date_creation');
        $order = $request->query('order', 'desc');
        $allowedSorts = ['date_creation', 'solde', 'titulaire_compte'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $order);
        }

        // Pagination
        $limit = min((int) $request->query('limit', 10), 100);
        $page = max((int) $request->query('page', 1), 1);
        return $query->paginate($limit, ['*'], 'page', $page);
    }
}
