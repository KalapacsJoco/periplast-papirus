<?php

namespace Database\Seeders;

use App\Models\Additive;
use App\Models\Color;
use App\Models\Insert;
use App\Models\Material;
use App\Models\Mould;
use App\Models\Product;
use Illuminate\Database\Seeder;

class MouldSeeder extends Seeder
{
    private const MATERIALS = [
        ['name' => 'PP copolymer', 'code' => 'RM-200001-0001'],
        ['name' => 'ABS',          'code' => 'RM-200001-0002'],
        ['name' => 'PA6',          'code' => 'RM-200001-0003'],
        ['name' => 'PC',           'code' => 'RM-200001-0004'],
    ];

    private const COLORS = [
        ['name' => 'Natúr', 'code' => 'CL-300001-0001'],
        ['name' => 'Fekete', 'code' => 'CL-300001-0002'],
        ['name' => 'Fehér',  'code' => 'CL-300001-0003'],
        ['name' => 'Kék',    'code' => 'CL-300001-0004'],
        ['name' => 'Piros',  'code' => 'CL-300001-0005'],
    ];

    private const ADDITIVES = [
        ['name' => 'Laser additive',     'code' => 'AD-400001-0001'],
        ['name' => 'UV stabilizator',    'code' => 'AD-400001-0002'],
        ['name' => 'Utesallosag novelő', 'code' => 'AD-400001-0003'],
        ['name' => 'Csuszast segito',     'code' => 'AD-400001-0004'],
    ];

    private const PRODUCTS = [
        ['name' => 'Fedel',            'code' => 'AA-100001-0001'],
        ['name' => 'Tartalyhaz',       'code' => 'AA-100001-0002'],
        ['name' => 'Csatlakozotest',   'code' => 'AA-100001-0003'],
        ['name' => 'Fogaskerek',       'code' => 'AA-100001-0004'],
        ['name' => 'Kapcselem',        'code' => 'AA-100001-0005'],
        ['name' => 'Burkolat',         'code' => 'AA-100001-0006'],
        ['name' => 'Sziphon idom',     'code' => 'AA-100001-0007'],
        ['name' => 'Szelephej',        'code' => 'AA-100001-0008'],
        ['name' => 'Rogzitobetet',     'code' => 'AA-100001-0009'],
        ['name' => 'Tartokonzol',      'code' => 'AA-100001-0010'],
        ['name' => 'Tomitogyuru',      'code' => 'AA-100001-0011'],
        ['name' => 'Csuszoelem',       'code' => 'AA-100001-0012'],
    ];

    public function run(): void
    {
        Insert::query()->delete();
        Mould::query()->delete();
        Product::query()->delete();
        Additive::query()->delete();
        Color::query()->delete();
        Material::query()->delete();

        foreach (self::MATERIALS as $data) {
            Material::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'is_active' => true,
            ]);
        }

        foreach (self::COLORS as $data) {
            Color::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'is_active' => true,
            ]);
        }

        foreach (self::ADDITIVES as $data) {
            Additive::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'is_active' => true,
            ]);
        }

        // Termékek létrehozása
        foreach (self::PRODUCTS as $data) {
            $product = Product::create([
                'name'      => $data['name'],
                'code'      => $data['code'],
                'material_id' => Material::query()->inRandomOrder()->value('id'),
                'color_id' => Color::query()->inRandomOrder()->value('id'),
                'is_active' => true,
            ]);

            $additiveIds = Additive::query()->inRandomOrder()->limit(rand(0, 2))->pluck('id')->all();
            $product->additives()->sync($additiveIds);
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
                    'product_id'  => $mould->products()->inRandomOrder()->value('products.id') ?? Product::query()->inRandomOrder()->value('id'),
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
