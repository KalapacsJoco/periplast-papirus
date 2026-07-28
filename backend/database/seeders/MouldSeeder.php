<?php

namespace Database\Seeders;

use App\Models\Insert;
use App\Models\Mould;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MouldSeeder extends Seeder
{
    private const PRODUCTS = [
        ['name' => 'Fedél',            'code' => 'PROD-001'],
        ['name' => 'Tartályház',       'code' => 'PROD-002'],
        ['name' => 'Csatlakozótest',   'code' => 'PROD-003'],
        ['name' => 'Fogaskerék',       'code' => 'PROD-004'],
        ['name' => 'Kapcselem',        'code' => 'PROD-005'],
        ['name' => 'Burkolat',         'code' => 'PROD-006'],
        ['name' => 'Sziphon idom',     'code' => 'PROD-007'],
        ['name' => 'Szelephéj',        'code' => 'PROD-008'],
        ['name' => 'Rögzítőbetét',     'code' => 'PROD-009'],
        ['name' => 'Tartókonzol',      'code' => 'PROD-010'],
        ['name' => 'Tömítőgyűrű',      'code' => 'PROD-011'],
        ['name' => 'Csúszóelem',       'code' => 'PROD-012'],
    ];

    public function run(): void
    {
        Insert::query()->delete();
        Mould::query()->delete();
        Product::query()->delete();

        // Termékek létrehozása
        foreach (self::PRODUCTS as $data) {
            Product::create([
                'name'      => $data['name'],
                'code'      => $data['code'],
                'is_active' => true,
            ]);
        }

        // 100 szerszám létrehozása
        $moulds = Mould::factory()->count(100)->create();

        // ~22 véletlenszerű szerszámhoz 2-3 betét hozzáadása
        $moulds->random(22)->each(function (Mould $mould) {
            $insertCount = rand(2, 3);
            $usedNumbers = [];

            for ($i = 1; $i <= $insertCount; $i++) {
                do {
                    $number = strtoupper(fake()->bothify('B-###'));
                } while (in_array($number, $usedNumbers));

                $usedNumbers[] = $number;

                Insert::create([
                    'mould_id'    => $mould->id,
                    'product_id'  => $mould->product_id,
                    'name'        => $i . '. betét',
                    'number'      => $number,
                    'description' => fake()->optional(0.5)->sentence(),
                    'status'      => fake()->randomElement([
                        'active', 'active',
                        'standby',
                        'warning',
                        'needs_repair',
                    ]),
                ]);
            }
        });
    }
}
