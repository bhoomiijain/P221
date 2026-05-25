<?php

namespace Database\Seeders;

use App\Models\HealthTip;
use App\Models\ShopBanner;
use App\Models\ShopTestimonial;
use Illuminate\Database\Seeder;

class ShopContentSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            ['title' => '20% Off Pain Relief', 'subtitle' => 'Limited time offer', 'code' => 'PAIN20', 'color' => 'teal', 'sort_order' => 1],
            ['title' => 'Free Delivery ₹500+', 'subtitle' => 'On all orders', 'code' => 'FREESHIP', 'color' => 'rose', 'sort_order' => 2],
            ['title' => 'First Order 15% Off', 'subtitle' => 'New customers', 'code' => 'WELCOME15', 'color' => 'amber', 'sort_order' => 3],
        ];

        foreach ($banners as $b) {
            ShopBanner::updateOrCreate(
                ['code' => $b['code']],
                [...$b, 'active' => true, 'link' => '/shop/medicines']
            );
        }

        $tips = [
            ['title' => 'Stay Hydrated', 'body' => 'Drink 8 glasses of water daily for better medication absorption.', 'icon' => 'droplet', 'sort_order' => 1],
            ['title' => 'Store Medicines Properly', 'body' => 'Keep medicines in cool, dry places away from direct sunlight.', 'icon' => 'shield', 'sort_order' => 2],
            ['title' => 'Complete Antibiotic Course', 'body' => 'Never stop antibiotics early even if you feel better.', 'icon' => 'pill', 'sort_order' => 3],
        ];

        foreach ($tips as $t) {
            HealthTip::updateOrCreate(['title' => $t['title']], [...$t, 'active' => true]);
        }

        $testimonials = [
            ['name' => 'Priya S.', 'text' => 'Fast delivery and genuine medicines. Highly recommended!', 'rating' => 5, 'sort_order' => 1],
            ['name' => 'Rahul M.', 'text' => 'AI consultant helped me avoid a wrong combination. Safe platform.', 'rating' => 5, 'sort_order' => 2],
            ['name' => 'Anita K.', 'text' => 'Easy prescription upload and tracking. Great shop experience.', 'rating' => 4, 'sort_order' => 3],
        ];

        foreach ($testimonials as $t) {
            ShopTestimonial::updateOrCreate(
                ['name' => $t['name'], 'text' => $t['text']],
                [...$t, 'active' => true]
            );
        }
    }
}
