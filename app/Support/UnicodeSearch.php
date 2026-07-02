<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

final class UnicodeSearch
{
    public static function term(?string $term): ?string
    {
        $term = trim((string) $term);

        return $term === '' ? null : $term;
    }

    public static function contains(?string $haystack, ?string $needle): bool
    {
        $needle = self::term($needle);

        if ($needle === null) {
            return true;
        }

        if ($haystack === null) {
            return false;
        }

        return mb_stripos($haystack, $needle, 0, 'UTF-8') !== false;
    }

    public static function containsAny(iterable $haystacks, ?string $needle): bool
    {
        foreach ($haystacks as $haystack) {
            if (self::contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    public static function paginate(Collection $items, int $perPage, ?int $page = null): LengthAwarePaginator
    {
        $page = $page ?: Paginator::resolveCurrentPage();
        $items = $items->values();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }
}
