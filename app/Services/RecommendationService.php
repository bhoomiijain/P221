<?php

namespace App\Services;

use App\Models\Medicine;

class RecommendationService
{
    public function __construct(private CustomerCatalogService $catalog) {}

    public function forMedicine(?string $medicineId, int $limit = 6): array
    {
        if (! $medicineId) {
            return $this->featured($limit);
        }

        $medicine = Medicine::find($medicineId);
        if (! $medicine) {
            return $this->featured($limit);
        }

        $similar = Medicine::query()
            ->where('category_id', $medicine->category_id)
            ->where('_id', '!=', $medicine->getKey())
            ->limit($limit)
            ->get();

        return $similar->map(fn ($m) => $this->catalog->formatMedicine($m))->values()->all();
    }

    public function featured(int $limit = 8): array
    {
        $featured = Medicine::query()->where('is_featured', true)->limit($limit)->get();
        if ($featured->isEmpty()) {
            $featured = Medicine::query()->limit($limit)->get();
        }

        return $featured->map(fn ($m) => $this->catalog->formatMedicine($m))->values()->all();
    }

    public function trending(int $limit = 8): array
    {
        return Medicine::query()
            ->orderByDesc('popularity')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => $this->catalog->formatMedicine($m))
            ->values()
            ->all();
    }

    public function frequentlyBoughtTogether(string $medicineId, int $limit = 4): array
    {
        return Medicine::query()
            ->where('_id', '!=', $medicineId)
            ->orderByDesc('popularity')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => $this->catalog->formatMedicine($m))
            ->values()
            ->all();
    }

    public function seasonal(int $limit = 4): array
    {
        $month = now()->month;
        $seasonalCategories = match (true) {
            in_array($month, [11, 12, 1, 2]) => ['Allergy Medicine', 'Pain Relief'],
            in_array($month, [6, 7, 8, 9]) => ['Vitamins', 'Allergy Medicine'],
            default => ['Pain Relief', 'Antibiotics'],
        };

        return Medicine::query()
            ->with('category')
            ->get()
            ->filter(fn ($m) => in_array($m->category?->name, $seasonalCategories, true))
            ->take($limit)
            ->map(fn ($m) => $this->catalog->formatMedicine($m))
            ->values()
            ->all();
    }
}
