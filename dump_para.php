<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$med = App\Models\Medicine::where('name', 'like', 'Para%')->first();
echo "Paracetamol ID: " . $med->id . "\n";
print_r(App\Models\Batch::where('medicine_id', $med->id)->get(['user_id', 'quantity', 'selling_price'])->toArray());
