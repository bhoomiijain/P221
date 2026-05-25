<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Supplier;
use App\Models\SupplierMedicine;
use App\Models\User;
use App\Support\MedicineImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CustomerCatalogSeeder::class);
        // ── Demo Pharmacist ─────────────────────────────────────────────────
        User::query()->updateOrCreate(
            ['email' => 'demo@pharmacy.local'],
            [
                'name'     => 'Demo Pharmacist',
                'password' => Hash::make('password'),
                'role'     => 'pharmacist',
                'theme'    => 'light',
            ]
        );

        // ── Master Categories (never deletable, visible to all users) ───────
        $painRelief  = Category::query()->updateOrCreate(
            ['name' => 'Pain Relief'],
            ['type' => 'master', 'user_id' => null]
        );
        $antibiotics = Category::query()->updateOrCreate(
            ['name' => 'Antibiotics'],
            ['type' => 'master', 'user_id' => null]
        );
        $allergyMed  = Category::query()->updateOrCreate(
            ['name' => 'Allergy Medicine'],
            ['type' => 'master', 'user_id' => null]
        );

        // ── Medicines ───────────────────────────────────────────────────────
        $paracetamol = Medicine::query()->updateOrCreate(
            ['name' => 'Paracetamol 500mg'],
            [
                'category_id' => $painRelief->getKey(),
                'description' => 'Tablet',
                'search_key'  => 'paracetamol 500mg',
                'image'       => MedicineImages::url('Paracetamol 500mg', 'Crocin'),
            ]
        );

        $ibuprofen = Medicine::query()->updateOrCreate(
            ['name' => 'Ibuprofen 400mg'],
            [
                'category_id' => $painRelief->getKey(),
                'description' => 'Tablet',
                'search_key'  => 'ibuprofen 400mg',
                'image'       => MedicineImages::url('Ibuprofen 400mg', 'Brufen'),
            ]
        );

        $amoxicillin = Medicine::query()->updateOrCreate(
            ['name' => 'Amoxicillin 250mg'],
            [
                'category_id' => $antibiotics->getKey(),
                'description' => 'Capsule',
                'search_key'  => 'amoxicillin 250mg',
                'image'       => MedicineImages::url('Amoxicillin 250mg', 'Novamox'),
            ]
        );

        $azithromycin = Medicine::query()->updateOrCreate(
            ['name' => 'Azithromycin 500mg'],
            [
                'category_id' => $antibiotics->getKey(),
                'description' => 'Tablet',
                'search_key'  => 'azithromycin 500mg',
                'image'       => MedicineImages::url('Azithromycin 500mg', 'Azee'),
            ]
        );

        $cetirizine = Medicine::query()->updateOrCreate(
            ['name' => 'Cetirizine 10mg'],
            [
                'category_id' => $allergyMed->getKey(),
                'description' => 'Tablet',
                'search_key'  => 'cetirizine 10mg',
                'image'       => MedicineImages::url('Cetirizine 10mg', 'Alerid'),
            ]
        );

        // ── Demo Supplier ───────────────────────────────────────────────────
        $supplierUser = User::query()->updateOrCreate(
            ['email' => 'demo-supplier@pharmacy.local'],
            [
                'name'     => 'Metro Supplier',
                'password' => Hash::make('password'),
                'role'     => 'supplier',
                'theme'    => 'light',
            ]
        );

        Supplier::query()->updateOrCreate(
            ['name' => 'Metro Medical Supply'],
            [
                'user_id'      => $supplierUser->getKey(),
                'contact_info' => 'accounts@metro-med.example',
                'email'        => 'demo-supplier@pharmacy.local',
                'search_key'   => 'metro medical supply',
            ]
        );

        // ── Supplier stock ──────────────────────────────────────────────────
        $supplierId = $supplierUser->getKey();
        $stockItems = [
            [$paracetamol,  5000, 1.00],
            [$ibuprofen,    3000, 1.50],
            [$amoxicillin,  2000, 3.50],
            [$azithromycin, 1500, 5.00],
            [$cetirizine,   4000, 0.80],
        ];
        foreach ($stockItems as [$med, $qty, $price]) {
            SupplierMedicine::query()->updateOrCreate(
                ['supplier_id' => $supplierId, 'medicine_id' => $med->getKey()],
                ['quantity' => $qty, 'price' => $price]
            );
        }

        // ── Pharmacy sample batches ─────────────────────────────────────────
        Batch::query()->updateOrCreate(
            ['medicine_id' => $paracetamol->getKey(), 'batch_number' => 'PCM-A1'],
            ['expiry_date' => now()->addMonths(8), 'quantity' => 120, 'purchase_price' => 1.20, 'selling_price' => 2.50]
        );
        Batch::query()->updateOrCreate(
            ['medicine_id' => $amoxicillin->getKey(), 'batch_number' => 'AMX-B1'],
            ['expiry_date' => now()->addDays(24), 'quantity' => 8, 'purchase_price' => 4.00, 'selling_price' => 7.50]
        );
    }
}
