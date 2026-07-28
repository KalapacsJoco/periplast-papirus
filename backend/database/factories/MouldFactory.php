<?php

namespace Database\Factories;

use App\Models\Mould;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mould>
 */
class MouldFactory extends Factory
{
    /** Tipikus fröccsöntő szerszámtípusok */
    private const TYPES = [
        'Kétlapos szerszám',
        'Háromlapos szerszám',
        'Csúszkás szerszám',
        'Liftes szerszám',
        'Hevederes szerszám',
    ];

    /** Tipikus fészekszámok fröccsöntő szerszámokhoz */
    private const CAVITY_COUNTS = [1, 2, 4, 4, 4, 8, 8, 16, 32];

    /** Tipikus szerszámnyitás-értékek mm-ben (min, max) */
    private const OPEN_MM_RANGES = [
        [100, 200],
        [150, 300],
        [200, 400],
        [250, 500],
        [300, 600],
        [350, 700],
    ];

    public function definition(): array
    {
        $range    = fake()->randomElement(self::OPEN_MM_RANGES);
        $openMm   = fake()->numberBetween($range[0], $range[1]);
        $cavities = fake()->randomElement(self::CAVITY_COUNTS);
        $mouldNo  = strtoupper(fake()->bothify('SZ-####'));
        $type     = fake()->randomElement(self::TYPES);

        return [
            'product_id'   => Product::inRandomOrder()->first()?->id ?? Product::factory(),
            'name'         => sprintf('%s %d-fészkes szerszám', $mouldNo, $cavities),
            'type'         => $type,
            'number'       => $mouldNo,
            'cavity_count' => $cavities,
            'open_mm'      => $openMm,
            'description'  => sprintf(
                '%s, %d fészkes, nyitás: %d mm.',
                $type, $cavities, $openMm,
            ),
            'status' => fake()->randomElement([
                'active', 'active', 'active',
                'standby', 'standby',
                'warning',
                'needs_repair',
                'under_repair',
            ]),
        ];
    }
}
