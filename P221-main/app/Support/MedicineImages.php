<?php

namespace App\Support;

class MedicineImages
{
    private const SLUGS = [
        'paracetamol' => 'paracetamol',
        'ibuprofen' => 'ibuprofen',
        'amoxicillin' => 'amoxicillin',
        'azithromycin' => 'azithromycin',
        'cetirizine' => 'cetirizine',
        'vitamin' => 'vitamin',
        'metformin' => 'metformin',
        'omeprazole' => 'omeprazole',
        'dolo' => 'dolo',
        'combiflam' => 'combiflam',
        'montelukast' => 'montelukast',
    ];

    public static function url(?string $name, ?string $brand = null, ?string $existing = null): string
    {
        if ($existing && str_contains($existing, '/images/medicines/') && str_ends_with($existing, '.svg')) {
            return $existing;
        }

        $text = strtolower(($name ?? '').' '.($brand ?? ''));
        foreach (self::SLUGS as $key => $slug) {
            if (str_contains($text, $key)) {
                return "/images/medicines/{$slug}.svg";
            }
        }

        return '/images/medicines/default.svg';
    }
}
