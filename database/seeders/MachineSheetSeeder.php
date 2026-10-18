<?php

namespace Database\Seeders;

use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Data transcribed from the "Maschinenbelegungsplan" sheet dated 13.08.2025.
 */
class MachineSheetSeeder extends Seeder
{
    private const SHEET_DATE = '2025-08-13';

    // machine, type, product, gross g, net g, order no, article no, required qty, previous-day count, cavities, planned cycle s, actual cycle s
    private const ROWS = [
        ['AP10', '470C-1500-800', 'KS1 weiss(RV-Madeira)(2-fach)', 22.26, 22.26, '18280', '00-13008-0000', 25200, 6112, 2, 23.0, 20.0],
        ['AP11', '470C-1500-400', '7S84 beige 1740/P468', null, null, '18253', '00-12123-0000', 28800, null, 4, null, 22.8],
        ['AP12', '470C-1500-400', 'KSM grün P348CRG(4-fach)', 7.49, 7.49, '18224', '00-13113-0000', 134400, 129312, 4, 13.1, 16.0],
        ['AP13', '520C-2000-675', 'FS1 rot 201(PP-Copo)', 37.72, 37.72, '18215', '00-13081-0000', 30000, 13152, 4, 35.1, 29.6],
        ['AP14', '570C-2000-800', 'FS0 rot 201', 15.43, 15.43, '18191', '00-13076-0000', 151200, 31504, 8, 18.4, 19.6],
        ['AP15', '570C-2000-800', 'M1022D4G weiss', 232.66, 232.66, '18107', '01-19052-0000', 551, null, 1, 50.3, 55.0],
        ['AP20', '320C-500-170', 'A900 weiss', 5.16, 4.84, '18102', '00-11063-0000', 108000, 103272, 4, 14.4, 15.0],
        ['AP21', 'KM 80/250 FX', 'PW500 weissL(00-71914)', 6.93, 6.58, '18098', '00-11269-0000', 108000, 77088, 4, 18.6, 17.0],
        ['AP22', '370E 600-170', 'EtaL95T gelb HFT_unbeschichtet', 5.26, 4.52, '17988', '01-54014-0000', 180000, 126040, 2, 12.0, 15.0],
        ['AP24', 'KM80CX-380SP', 'KSMG grau 2504RG(2-fach)', 7.34, 7.34, '18254', '00-13046-0000', 57600, null, 2, 15.2, 16.0],
        ['AP25', '6305-2500-1300', 'C114 orange 3943 HFT', 13.67, 13.67, '18131', '00-14110-0000', 90000, null, 8, 19.5, 17.0],
        ['AP27', '520E-1500-400', 'A200 weiss(20% 00-71966)', 2.99, 2.76, '18070', '00-11254-0000', 630000, 169400, 4, 10.3, 10.2],
        ['AP30', '320C-500-250', 'A200 weiss(20% 00-71966)', 3.49, 2.72, '18065', '00-11254-0000', 504000, 440060, 4, 16.7, 10.2],
        ['AP32', '420C-1000-290', 'SP8 natur', null, null, '18242', '00-20306-0000', 10120, null, 2, null, 25.0],
        ['AP33', '420M-1000-350', 'MOD schwarz(00-71995)', null, null, '18111', '00-19053-0000', 785, null, 2, null, 19.0],
        ['AP34', '420C-1000-350', 'ZM2000KSM weiss', null, null, '17605', '00-19004-0000', 54600, null, 6, null, 12.8],
        ['AP36', '270M-250-75-H', 'L150 rot 2290(PP-Copo)', 9.54, 8.71, '18188', '00-20047-0000', 43200, 21530, 2, 16.4, 17.3],
    ];

    // machines without a running order on the sheet
    private const IDLE_MACHINES = [
        ['AP00', '370V-500-170'],
        ['AP01', '370V-500-170'],
        ['AP16', 'MA IIS 1600-570-845'],
        ['AP23', '420C-1000-290'],
        ['AP31', '370E 600-170'],
        ['AP37', '320C-500-250-H'],
    ];

    public function run(): void
    {
        foreach (self::IDLE_MACHINES as [$name, $type]) {
            Machine::firstOrCreate(['name' => $name], ['type' => $type]);
        }

        $usedNumbers = [];

        foreach (self::ROWS as [$machineName, $type, $productName, $gross, $net, $orderNo, $articleNo, $required, $previousDay, $cavities, $plannedCycle, $actualCycle]) {
            $machine = Machine::firstOrCreate(['name' => $machineName], ['type' => $type]);

            $product = Product::firstOrCreate(
                ['code' => $articleNo],
                [
                    'name' => $productName,
                    'planned_cycle_time' => $plannedCycle !== null ? (int) round($plannedCycle * 1000) : null,
                ]
            );

            do {
                $number = sprintf('00-8%04d-0000', random_int(0, 9999));
            } while (isset($usedNumbers[$number]) || Mould::where('number', $number)->exists());
            $usedNumbers[$number] = true;

            $mould = Mould::create([
                'name' => $productName,
                'type' => 'Fröccsszerszám',
                'number' => $number,
                'cavity_count' => $cavities,
            ]);

            $mould->products()->attach($product);
            $mould->machines()->attach($machine);

            $order = Order::create([
                'order_number' => $orderNo,
                'machine_id' => $machine->id,
                'mould_id' => $mould->id,
                'product_id' => $product->id,
                'date' => self::SHEET_DATE,
                'customer' => '',
                'quantity' => $required,
                'cycle_time' => (int) round($actualCycle * 1000),
                'gross_weight' => $gross,
                'net_weight' => $net,
                'status' => $previousDay !== null ? 'in_progress' : 'waiting',
            ]);

            if ($previousDay !== null) {
                $order->dailyCounts()->create([
                    'date' => date('Y-m-d', strtotime(self::SHEET_DATE.' -1 day')),
                    'count' => $previousDay,
                ]);
            }
        }
    }
}
