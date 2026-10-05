<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Búsqueda y ordenamiento comunes a todos los listados del panel.
 *
 *   Model::query()
 *       ->whereSearch($search, ['name', 'description', 'id', 'country.name'])
 *       ->sortable(['id' => 'id', 'name' => 'name', 'country' => fn ($q, $dir) => ...], 'name')
 */
class QueryMacros
{
    public static function register(): void
    {
        /*
         * Búsqueda sin importar tildes, ñ ni mayúsculas ("electrica" encuentra "Eléctrica").
         * Cada palabra escrita debe aparecer en alguna de las columnas, en cualquier orden:
         * "juan perez" encuentra a Juan (name) Pérez (father_last_name).
         * "relacion.columna" busca en una tabla relacionada (p. ej. 'country.name'),
         * también anidada ('secondaryCategory.primaryCategory.name').
         * Requiere la extensión unaccent de PostgreSQL.
         */
        Builder::macro('whereSearch', function (?string $term, array $columns) {
            /** @var Builder $this */
            $words = preg_split('/\s+/u', trim((string) $term), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($words as $word) {
                // % y _ se buscan como texto, no como comodines
                $like = '%' . addcslashes($word, '\\%_') . '%';

                $this->where(function (Builder $query) use ($columns, $like) {
                    foreach ($columns as $column) {
                        if (str_contains($column, '.')) {
                            // "secondaryCategory.primaryCategory.name" → relación anidada + columna
                            $pos = strrpos($column, '.');
                            $relation = substr($column, 0, $pos);
                            $relatedColumn = substr($column, $pos + 1);
                            $query->orWhereHas($relation, function (Builder $related) use ($relatedColumn, $like) {
                                $related->whereRaw(QueryMacros::unaccentLike($related, $relatedColumn), [$like]);
                            });
                        } else {
                            $query->orWhereRaw(QueryMacros::unaccentLike($query, $column), [$like]);
                        }
                    }
                });
            }

            return $this;
        });

        /*
         * Ordena según ?sort=columna&direction=asc|desc (los encabezados <x-sort-th>).
         * Solo se aceptan las columnas de $columns: el valor puede ser una columna,
         * varias (array) o un Closure($query, $direction) para ordenar por otra tabla.
         */
        Builder::macro('sortable', function (array $columns, string $default, string $defaultDirection = 'asc') {
            /** @var Builder $this */
            $requested = request()->query('sort');
            $valid = is_string($requested) && array_key_exists($requested, $columns);

            $key = $valid ? $requested : $default;
            $direction = $valid
                ? (request()->query('direction') === 'desc' ? 'desc' : 'asc')
                : $defaultDirection;

            $sort = $columns[$key];
            if ($sort instanceof Closure) {
                $sort($this, $direction);
            } else {
                foreach ((array) $sort as $column) {
                    $this->orderBy($this->getModel()->qualifyColumn($column), $direction);
                }
            }

            // Desempate estable para que la paginación no repita ni salte filas
            return $this->orderBy($this->getModel()->getQualifiedKeyName(), $direction);
        });
    }

    /** "unaccent(CAST(tabla.columna AS TEXT)) ILIKE unaccent(?)" con la columna escapada */
    public static function unaccentLike(Builder $query, string $column): string
    {
        $wrapped = $query->getQuery()->getGrammar()->wrap($query->getModel()->qualifyColumn($column));

        return "unaccent(CAST({$wrapped} AS TEXT)) ILIKE unaccent(?)";
    }
}
