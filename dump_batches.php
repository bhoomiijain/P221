<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pharmacist = App\Models\User::where('role', 'pharmacist')->first();
echo "Pharmacist ID: " . $pharmacist->id . "\n";
echo "Batches:\n";
print_r(App\Models\Batch::where('user_id', $pharmacist->id)->get(['medicine_id', 'quantity', 'expiry_date', 'selling_price'])->toArray());
