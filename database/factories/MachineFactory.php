<?php

namespace Database\Factories;

use App\Models\Machine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Machine>
 */
class MachineFactory extends Factory
{
    /**
     * Curated Arburg machine data. Model names and key values are based on
     * public used-machine listings; values that are not always explicitly listed
     * are kept within realistic ranges for the given machine class.
     */
    private const ARBURG_VARIANTS = [
        ['model' => 'ALLROUNDER 275 V 250', 'open_mm' => [375, 475], 'screw_mm' => [30]],
        ['model' => 'ALLROUNDER 270 S 150-60 U', 'open_mm' => [300, 420], 'screw_mm' => [22, 25, 30]],
        ['model' => 'ALLROUNDER 270 S 350-60', 'open_mm' => [300, 420], 'screw_mm' => [25, 30]],
        ['model' => 'ALLROUNDER 320 C 500-170', 'open_mm' => [350, 500], 'screw_mm' => [30, 35]],
        ['model' => 'ALLROUNDER 320 C 600', 'open_mm' => [350, 520], 'screw_mm' => [30, 35, 40]],
        ['model' => 'ALLROUNDER 370 U 600-290', 'open_mm' => [420, 620], 'screw_mm' => [35, 40]],
        ['model' => 'ALLROUNDER 420 S 1300-150', 'open_mm' => [450, 700], 'screw_mm' => [30, 35]],
        ['model' => 'ALLROUNDER 470 C 1500-350', 'open_mm' => [500, 750], 'screw_mm' => [40, 45]],
        ['model' => 'ALLROUNDER 630 S 2500-675', 'open_mm' => [700, 1000], 'screw_mm' => [45]],
        ['model' => 'ALLROUNDER 630 S 2500-2100', 'open_mm' => [700, 1050], 'screw_mm' => [55, 60]],
        ['model' => 'ALLROUNDER 630 A 2500-800', 'open_mm' => [700, 1000], 'screw_mm' => [50, 55]],
        ['model' => 'ALLROUNDER 1200 T 800-150', 'open_mm' => [380, 520], 'screw_mm' => [30, 35]],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $variant = fake()->randomElement(self::ARBURG_VARIANTS);
        $openMm = fake()->numberBetween($variant['open_mm'][0], $variant['open_mm'][1]);
        $screwMm = fake()->randomElement($variant['screw_mm']);
        $machineNo = fake()->unique()->numberBetween(1, 999);

        return [
            'name' => sprintf('Arburg %s #%03d', $variant['model'], $machineNo),
            'type' => sprintf('Arburg %s', $variant['model']),
            'open_mm' => $openMm,
            'screw_diameter_cm' => round($screwMm / 10, 2),
            'description' => sprintf(
                'Arburg %s alapú fröccsöntőgép. Nyitás: %d mm, csigaátmérő: %d mm.',
                $variant['model'],
                $openMm,
                $screwMm,
            ),
            'status' => fake()->randomElement(['active', 'active', 'active', 'maintenance', 'inactive']),
        ];
    }
}
