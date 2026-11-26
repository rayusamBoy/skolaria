<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Lga;
use App\Models\Nationality;
use App\Models\State;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ReferenceController extends Controller
{
    /**
     * List all countries.
     */
    public function countries(): JsonResponse
    {
        $perPage = $this->perPage();

        $countries = Cache::remember(
            $this->paginationCacheKey('reference:countries', $perPage),
            now()->addDay(),
            fn () => Nationality::query()
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString()
        );

        return response()->json($countries);
    }

    /**
     * List states belonging to a country.
     */
    public function states(string $countryCode): JsonResponse
    {
        $nationality = $this->findCountry($countryCode);

        if (! $nationality) {
            return $this->notFound('Country not found.');
        }

        $perPage = $this->perPage();

        $states = Cache::remember(
            $this->paginationCacheKey(
                "reference:country:{$nationality->id}:states",
                $perPage
            ),
            now()->addDay(),
            fn () => State::query()
                ->where('country_id', $nationality->id)
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString()
        );

        return response()->json($states);
    }

    /**
     * List LGAs belonging to a state.
     */
    public function lgas(int $state): JsonResponse
    {
        if (! State::whereKey($state)->exists()) {
            return $this->notFound('State not found.');
        }

        $perPage = $this->perPage();

        $lgas = Cache::remember(
            $this->paginationCacheKey(
                "reference:state:{$state}:lgas",
                $perPage
            ),
            now()->addDay(),
            fn () => Lga::query()
                ->where('state_id', $state)
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString()
        );

        return response()->json($lgas);
    }

    /**
     * List all LGAs belonging to a country.
     */
    public function countryLgas(string $countryCode): JsonResponse
    {
        $nationality = $this->findCountry($countryCode);

        if (! $nationality) {
            return $this->notFound('Country not found.');
        }

        $perPage = $this->perPage();

        $lgas = Cache::remember(
            $this->paginationCacheKey(
                "reference:country:{$nationality->id}:lgas",
                $perPage
            ),
            now()->addDay(),
            fn () => Lga::query()
                ->whereIn('state_id', function ($query) use ($nationality) {
                    $query->select('id')
                        ->from((new State)->getTable())
                        ->where('country_id', $nationality->id);
                })
                ->orderBy('name')
                ->paginate($perPage)
                ->withQueryString()
        );

        return response()->json($lgas);
    }

    /**
     * Resolve a country using its ISO-2 or ISO-3 code.
     */
    private function findCountry(string $code): ?Nationality
    {
        $code = strtoupper(trim($code));

        return Nationality::query()
            ->whereRaw('UPPER(iso2) = ?', [$code])
            ->orWhereRaw('UPPER(iso3) = ?', [$code])
            ->first();
    }

    private function notFound(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 404);
    }

    /**
     * Get the requested page size, capped at 100 records.
     */
    private function perPage(): int
    {
        return min(
            max((int) request()->query('per_page', 50), 1),
            100
        );
    }

    /**
     * Generate a cache key for a paginated result.
     */
    private function paginationCacheKey(string $key, int $perPage): string
    {
        return "{$key}:per-page:{$perPage}:page:".request()->query('page', 1);
    }
}
