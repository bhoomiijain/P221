<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find('6a0b148826614940f8052962');
auth()->login($user);

$req = new \Illuminate\Http\Request();
$req->merge(['q' => 'para']);
$controller = app(App\Http\Controllers\Api\InventoryController::class);
$inv = app(App\Services\InventoryService::class);

echo $controller->search($req, $inv)->content();
