<?php

namespace App\Services;

use App\Support\MedicineImages;
use App\Models\Batch;
use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Public catalog for customer shop — aggregates stock from all pharmacy batches.
 */
class CustomerCatalogService
{
    public function availableStock(string $medicineId): int
    {
        return (int) Batch::query()
            ->where('medicine_id', $medicineId)
            ->where('expiry_date', '>=', today())
            ->sum('quantity');
    }

    public function formatMedicine(Medicine $medicine, bool $detailed = false): array
    {
        $stock = $this->availableStock($medicine->getKey());
        $data = [
            'id' => $medicine->getKey(),
            'name' => $medicine->name,
            'manufacturer' => $medicine->manufacturer ?? 'Generic Pharma',
            'brand' => $medicine->brand ?? $medicine->manufacturer ?? 'HealthPlus',
            'image' => MedicineImages::url($medicine->name, $medicine->brand, $medicine->image),
            'category_id' => $medicine->category_id,
            'category' => $medicine->category?->name,
            'description' => $medicine->description,
            'form' => $medicine->form ?? 'Tablet',
            'pack_size' => $medicine->pack_size ?? '10 tablets',
            'mrp' => (float) ($medicine->mrp ?? 99),
            'discount_percent' => (int) ($medicine->discount_percent ?? 0),
            'price' => $medicine->sellingPrice(),
            'prescription_required' => (bool) ($medicine->prescription_required ?? false),
            'rating_avg' => (float) ($medicine->rating_avg ?? 4.2),
            'review_count' => (int) ($medicine->review_count ?? 0),
            'popularity' => (int) ($medicine->popularity ?? 0),
            'in_stock' => $stock > 0,
            'stock' => $stock,
            'is_featured' => (bool) ($medicine->is_featured ?? false),
            'is_trending' => (bool) ($medicine->is_trending ?? false),
        ];

        if ($detailed) {
            $data += [
                'ingredients' => $medicine->ingredients ?? 'See package insert.',
                'dosage' => $medicine->dosage ?? 'As directed by physician.',
                'side_effects' => $medicine->side_effects ?? 'Mild nausea, drowsiness may occur.',
                'warnings' => $medicine->warnings ?? 'Keep out of reach of children.',
                'nearest_expiry' => $this->nearestExpiry($medicine->getKey()),
            ];
        }

        return $data;
    }

    public function nearestExpiry(string $medicineId): ?string
    {
        $batch = Batch::query()
            ->where('medicine_id', $medicineId)
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', today())
            ->orderBy('expiry_date')
            ->first();

        return $batch?->expiry_date?->format('M Y');
    }

    public function list(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = Medicine::query()->with('category');

        if (! empty($filters['q'])) {
            $q = strtolower($filters['q']);
            $query->where(function ($builder) use ($q) {
                $builder->where('search_key', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }

        if (isset($filters['prescription_required'])) {
            $query->where('prescription_required', (bool) $filters['prescription_required']);
        }

        if (! empty($filters['min_price']) || ! empty($filters['max_price'])) {
            $query->whereNotNull('mrp');
        }

        $medicines = $query->get();

        if (! empty($filters['min_price'])) {
            $medicines = $medicines->filter(fn ($m) => $m->sellingPrice() >= (float) $filters['min_price']);
        }
        if (! empty($filters['max_price'])) {
            $medicines = $medicines->filter(fn ($m) => $m->sellingPrice() <= (float) $filters['max_price']);
        }

        if (isset($filters['in_stock']) && $filters['in_stock'] !== '') {
            $medicines = $medicines->filter(function ($m) use ($filters) {
                $inStock = $this->availableStock($m->getKey()) > 0;

                return $filters['in_stock'] === '1' ? $inStock : ! $inStock;
            });
        }

        $sort = $filters['sort'] ?? 'popularity';
        $medicines = match ($sort) {
            'price_asc' => $medicines->sortBy(fn ($m) => $m->sellingPrice()),
            'price_desc' => $medicines->sortByDesc(fn ($m) => $m->sellingPrice()),
            'rating' => $medicines->sortByDesc('rating_avg'),
            default => $medicines->sortByDesc('popularity'),
        };

        $page = max(1, (int) ($filters['page'] ?? 1));
        $total = $medicines->count();
        $items = $medicines->slice(($page - 1) * $perPage, $perPage)->values()
            ->map(fn ($m) => $this->formatMedicine($m));

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function categories(): Collection
    {
        return Category::query()
            ->where(function ($q) {
                $q->where('type', 'master')->orWhereNull('type');
            })
            ->get()
            ->map(fn ($c) => [
                'id' => $c->getKey(),
                'name' => $c->name,
                'count' => Medicine::where('category_id', $c->getKey())->count(),
            ]);
    }

    public function searchSuggestions(string $term, int $limit = 8): array
    {
        if (strlen($term) < 2) {
            return [];
        }

        return Medicine::query()
            ->where('search_key', 'like', '%'.strtolower($term).'%')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->getKey(),
                'name' => $m->name,
                'brand' => $m->brand,
            ])
            ->all();
    }
}
