<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Medicine;
use App\Models\User;
use App\Support\MedicineImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerCatalogSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'customer@pharmacy.local'],
            [
                'name' => 'Demo Customer',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '9876543210',
                'theme' => 'light',
            ]
        );

        $categories = [
            'Pain Relief' => Category::where('name', 'Pain Relief')->first(),
            'Antibiotics' => Category::where('name', 'Antibiotics')->first(),
            'Allergy Medicine' => Category::where('name', 'Allergy Medicine')->first(),
            'Vitamins' => Category::query()->updateOrCreate(
                ['name' => 'Vitamins'],
                ['type' => 'master', 'user_id' => null]
            ),
            'Diabetes Care' => Category::query()->updateOrCreate(
                ['name' => 'Diabetes Care'],
                ['type' => 'master', 'user_id' => null]
            ),
        ];

        $catalog = [
            ['Paracetamol 500mg', 'Pain Relief', 'Cipla', 'Crocin', 35, 10, false, true, true, 4.5, 128],
            ['Ibuprofen 400mg', 'Pain Relief', 'Abbott', 'Brufen', 55, 15, false, true, false, 4.3, 89],
            ['Amoxicillin 250mg', 'Antibiotics', 'GSK', 'Novamox', 120, 5, true, false, true, 4.6, 56],
            ['Azithromycin 500mg', 'Antibiotics', 'Cipla', 'Azee', 180, 12, true, true, true, 4.4, 72],
            ['Cetirizine 10mg', 'Allergy Medicine', 'Dr Reddy', 'Alerid', 45, 8, false, true, false, 4.7, 201],
            ['Vitamin C 500mg', 'Vitamins', 'Himalaya', 'Immuno', 199, 20, false, true, true, 4.8, 310],
            ['Vitamin D3 60k', 'Vitamins', 'Sun Pharma', 'Calcirol', 249, 10, false, false, true, 4.5, 145],
            ['Metformin 500mg', 'Diabetes Care', 'USV', 'Glycomet', 85, 5, true, true, false, 4.2, 67],
            ['Omeprazole 20mg', 'Pain Relief', 'Torrent', 'Omez', 95, 10, false, false, false, 4.4, 44],
            ['Dolo 650', 'Pain Relief', 'Micro Labs', 'Dolo', 32, 5, false, true, true, 4.9, 520],
            ['Combiflam', 'Pain Relief', 'Sanofi', 'Combiflam', 48, 8, false, true, false, 4.3, 178],
            ['Montelukast 10mg', 'Allergy Medicine', 'Cipla', 'Montair', 165, 10, true, false, false, 4.1, 33],
        ];

        foreach ($catalog as [$name, $catName, $mfr, $brand, $mrp, $disc, $rx, $featured, $trending, $rating, $pop]) {
            $cat = $categories[$catName] ?? null;
            $med = Medicine::query()->updateOrCreate(
                ['name' => $name],
                [
                    'category_id' => $cat?->getKey(),
                    'description' => "Quality {$brand} formulation for effective treatment.",
                    'search_key' => strtolower("{$name} {$brand} {$mfr}"),
                    'image' => MedicineImages::url($name, $brand),
                    'manufacturer' => $mfr,
                    'brand' => $brand,
                    'mrp' => $mrp,
                    'discount_percent' => $disc,
                    'prescription_required' => $rx,
                    'is_featured' => $featured,
                    'is_trending' => $trending,
                    'rating_avg' => $rating,
                    'review_count' => random_int(20, 500),
                    'popularity' => $pop,
                    'form' => 'Tablet',
                    'pack_size' => '10 tablets',
                    'ingredients' => 'Active pharmaceutical ingredients per IP standards.',
                    'dosage' => 'As directed by physician. Do not self-medicate.',
                    'side_effects' => 'Mild nausea, dizziness may occur in rare cases.',
                    'warnings' => 'Not for children under 12 without doctor advice.',
                ]
            );

            Batch::query()->updateOrCreate(
                ['medicine_id' => $med->getKey(), 'batch_number' => 'SHOP-'.substr(md5($name), 0, 6)],
                [
                    'expiry_date' => now()->addMonths(random_int(6, 18)),
                    'quantity' => random_int(50, 200),
                    'purchase_price' => $mrp * 0.6,
                    'selling_price' => round($mrp * (1 - $disc / 100), 2),
                ]
            );
        }

        foreach ([
            ['WELCOME15', 'percent', 15, 200],
            ['PAIN20', 'percent', 20, 150],
            ['HEALTH10', 'fixed', 10, 100],
            ['FREESHIP', 'fixed', 49, 500],
        ] as [$code, $type, $val, $min]) {
            Coupon::updateOrCreate(
                ['code' => $code],
                ['type' => $type, 'value' => $val, 'min_order' => $min, 'active' => true]
            );
        }
    }
}
