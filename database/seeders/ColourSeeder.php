<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class ColourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $records = [
            ['place' => '100', 'code' => '00-71035-0000', 'name' => 'PE-LDPE natur', 'description' => null],
            ['place' => '133', 'code' => '00-72002-0000', 'name' => 'PS-FARBE GELB 6130', 'description' => 'PAN 1345'],
            ['place' => '135', 'code' => '00-72004-0000', 'name' => 'UNI-FARBE ROT 200', 'description' => 'RAL3020'],
            ['place' => '125', 'code' => '00-72005-0000', 'name' => 'PS-FARBE GRAU 2522', 'description' => 'PAN 428C'],
            ['place' => '111', 'code' => '00-72006-0000', 'name' => 'PE-FARBE GELB 30224410', 'description' => 'RAL1032'],
            ['place' => '133', 'code' => '00-72008-0000', 'name' => 'UNI-FARBE GOLD METALLIC 595', 'description' => null],
            ['place' => '133', 'code' => '00-72014-0000', 'name' => 'PP-FARBE BLAU 4036', 'description' => 'PAN 284C'],
            ['place' => '112', 'code' => '00-72016-0000', 'name' => 'PS-FARBE BEIGE 1740', 'description' => null],
            ['place' => '143', 'code' => '00-72018-0000', 'name' => 'PA-FARBE BLAU 4656', 'description' => null],
            ['place' => '121', 'code' => '00-72019-0000', 'name' => 'PE-FARBE BLAU 1496', 'description' => 'PAN Reflex Blue C'],
            ['place' => '115', 'code' => '00-72020-0000', 'name' => 'PP-FARBE BLAU 1987', 'description' => 'PAN 3005C'],
            ['place' => '116', 'code' => '00-72028-0000', 'name' => 'PS-FARBE ELFENBEIN 768411', 'description' => 'PAN 1205C'],
            ['place' => '143', 'code' => '00-72031-0000', 'name' => 'PP-FARBE GELB 3271', 'description' => null],
            ['place' => '143', 'code' => '00-72032-0000', 'name' => 'rPP-FARBE GRAU 402420', 'description' => 'RAL 7033'],
            ['place' => '133', 'code' => '00-72033-0000', 'name' => 'UNI-FARBE GRAU 7007', 'description' => null],
            ['place' => '101', 'code' => '00-72043-0000', 'name' => 'PP-FARBE SCHWARZ 15283', 'description' => 'PAN 2C2X'],
            ['place' => '124', 'code' => '00-72047-0000', 'name' => 'PS-FARBE BRAUN 240480', 'description' => null],
            ['place' => '111', 'code' => '00-72048-0000', 'name' => 'PE-FARBE BLAU P2728C', 'description' => null],
            ['place' => '111', 'code' => '00-72048-0001', 'name' => 'UNI-FARBE BLAU P2728C', 'description' => null],
            ['place' => '125', 'code' => '00-72049-0000', 'name' => 'UNI-FARBE GELB 11510 / P1235C', 'description' => 'PAN 1235C'],
            ['place' => '113', 'code' => '00-72054-0000', 'name' => 'PP-FARBE ORANGE 2538', 'description' => null],
            ['place' => '102', 'code' => '00-72056-0000', 'name' => 'PP-FARBE WEISS', 'description' => null],
            ['place' => '105', 'code' => '00-72057-0000', 'name' => 'PS-FARBE WEISS 10236', 'description' => 'RAL 9003'],
            ['place' => '103', 'code' => '00-72058-0000', 'name' => 'PE-FARBE GRÜN 2880', 'description' => 'PAN 356C'],
            ['place' => '132', 'code' => '00-72060-0000', 'name' => 'PP-FARBE GRAU 2236', 'description' => 'PAN 429C'],
            ['place' => '132', 'code' => '00-72061-0000', 'name' => 'PP-FARBE ROSA 2237', 'description' => 'PAN 196C'],
            ['place' => '125', 'code' => '00-72063-0000', 'name' => 'PE-FARBE GELB 694', 'description' => 'PAN 107C'],
            ['place' => '101', 'code' => '00-72064-0000', 'name' => 'PP-FARBE ROT 2290', 'description' => 'PAN 1797C'],
            ['place' => '126', 'code' => '00-72066-0000', 'name' => 'PP-FARBE BLAU 4552', 'description' => 'PAN 2925C'],
            ['place' => '133', 'code' => '00-72068-0000', 'name' => 'PP-FARBE GRAU 3013', 'description' => 'PAN 430C'],
            ['place' => '114', 'code' => '00-72070-0000', 'name' => 'PE-FARBE GRÜN 7962/355C', 'description' => null],
            ['place' => '126', 'code' => '00-72071-0000', 'name' => 'PS-FARBE GRAU 115420', 'description' => 'RAL 7026'],
            ['place' => '126', 'code' => '00-72072-0000', 'name' => 'PS-FARBE GELB 7735', 'description' => 'PAN 560C'],
            ['place' => '135', 'code' => '00-72073-0000', 'name' => 'PP-FARBE BLAU 44302', 'description' => null],
            ['place' => '104', 'code' => '00-72074-0000', 'name' => 'PP-FARBE ROSA 243C', 'description' => 'PAN 243C'],
            ['place' => '131', 'code' => '00-72080-0000', 'name' => 'PE-FARBE BLAU 522223', 'description' => null],
            ['place' => '124', 'code' => '00-72081-0000', 'name' => 'PE-FARBE BLAU 445', 'description' => null],
            ['place' => '143', 'code' => '00-72083-0000', 'name' => 'PE-FARBE ROT 601493', 'description' => null],
            ['place' => '143', 'code' => '00-72090-0000', 'name' => 'PA-FARBE SCHWARZ 9472', 'description' => null],
            ['place' => '125', 'code' => '00-72093-0000', 'name' => 'PS-FARBE BRAUN 1961', 'description' => 'PAN 131C'],
            ['place' => '142', 'code' => '00-72096-0000', 'name' => 'PP-FARBE ROT 631481', 'description' => 'RAL 3020'],
            ['place' => '142', 'code' => '00-72097-0000', 'name' => 'PP-FARBE GRAU 2138', 'description' => null],
            ['place' => '135', 'code' => '00-72107-0000', 'name' => 'UNI-FARBE BLAU 4759', 'description' => 'PAN 283C'],
            ['place' => '134', 'code' => '00-72109-0000', 'name' => 'PP-FARBE ROT 4758', 'description' => null],
            ['place' => '112', 'code' => '00-72111-0000', 'name' => 'PS-FARBE BEIGE 3169', 'description' => 'PAN 719C'],
            ['place' => '123', 'code' => '00-72127-0000', 'name' => 'UNI-FARBE GRAU 7022', 'description' => 'PAN 728C'],
            ['place' => '134', 'code' => '00-72153-0000', 'name' => 'PP-FARBE VIOLETT 4627', 'description' => 'PAN 2627C'],
            ['place' => '134', 'code' => '00-72154-0000', 'name' => 'PP-FARBE GELB 46501', 'description' => null],
            ['place' => '134', 'code' => '00-72156-0000', 'name' => 'PP-FARBE GRAU 3498', 'description' => null],

            // 2/3. oldal
            ['place' => '132', 'code' => '00-72157-0000', 'name' => 'PP-FARBE BLAU 53389', 'description' => 'PAN 304C'],
            ['place' => '111', 'code' => '00-72158-0000', 'name' => 'PP-FARBE GRÜN 22952', 'description' => 'PAN 349C'],
            ['place' => '132', 'code' => '00-72172-0000', 'name' => 'PS-FARBE GRÜN 635404', 'description' => null],
            ['place' => '124', 'code' => '00-72176-0000', 'name' => 'PS-FARBE ROT 465404', 'description' => null],
            ['place' => '131', 'code' => '00-72177-0000', 'name' => 'PS-FARBE GRÜN 626404', 'description' => 'PAN 361C'],
            ['place' => '116', 'code' => '00-72189-0000', 'name' => 'UNI-FARBE GRÜN 5628', 'description' => null],
            ['place' => '114', 'code' => '00-72190-0000', 'name' => 'PP-FARBE BLAU 5021', 'description' => null],
            ['place' => '101', 'code' => '00-72195-0000', 'name' => 'UNI-FARBE BLAU 55648', 'description' => null],
            ['place' => '114', 'code' => '00-72198-0000', 'name' => 'PE-FARBE ORANGE 3943/166C', 'description' => 'PAN 166C'],
            ['place' => '115', 'code' => '00-72201-0000', 'name' => 'PP-FARBE ORANGE 5773', 'description' => null],
            ['place' => '135', 'code' => '00-72203-0000', 'name' => 'UNI-FARBE SILBER 702', 'description' => 'PAN 877C'],
            ['place' => '112', 'code' => '00-72204-0000', 'name' => 'UNI-FARBE SCHWARZ', 'description' => null],
            ['place' => '122', 'code' => '00-72206-0000', 'name' => 'PS-FARBE ORANGE 2618403', 'description' => 'PAN 1665C'],
            ['place' => '122', 'code' => '00-72207-0000', 'name' => 'PS-FARBE ROT 729411', 'description' => 'PAN 7427C'],
            ['place' => '122', 'code' => '00-72208-0000', 'name' => 'PS-FARBE GELB 160409', 'description' => null],
            ['place' => '135', 'code' => '00-72210-0000', 'name' => 'UNI-FARBE WEISS 5', 'description' => null],
            ['place' => '116', 'code' => '00-72211-0000', 'name' => 'UNI-FARBE GRAU 7035', 'description' => null],
            ['place' => '123', 'code' => '00-72213-0000', 'name' => 'UNI-FARBE BLAU 11', 'description' => 'RAL5005'],
            ['place' => '124', 'code' => '00-72214-0000', 'name' => 'PP-FARBE BLAU 52410', 'description' => null],
            ['place' => '121', 'code' => '00-72216-0000', 'name' => 'UNI-FARBE ROT 33038', 'description' => null],
            ['place' => '113', 'code' => '00-72217-0000', 'name' => 'UNI-FARBE GRÜN 6041', 'description' => null],
            ['place' => '123', 'code' => '00-72218-0000', 'name' => 'PE-FARBE BRAUN 85', 'description' => 'PAN 484C'],
            ['place' => '143', 'code' => '00-72223-0000', 'name' => 'PP-FARBE GRÜN 9014', 'description' => 'PAN 344C'],
            ['place' => '106', 'code' => '00-72224-0000', 'name' => 'PE-FARBE ROT 201', 'description' => null],
            ['place' => '122', 'code' => '00-72225-0000', 'name' => 'PE-FARBE ORANGE 204', 'description' => null],
            ['place' => '126', 'code' => '00-72226-0000', 'name' => 'PE-FARBE GRÜN 66239', 'description' => null],
            ['place' => '123', 'code' => '00-72228-0000', 'name' => 'UNI-FARBE BLAU 56693', 'description' => null],
            ['place' => '121', 'code' => '00-72235-0000', 'name' => 'PP-FARBE GRÜN 7263', 'description' => 'PAN 359C'],
            ['place' => '124', 'code' => '00-72237-0000', 'name' => 'PP-FARBE GRÜN 467413', 'description' => 'PAN 580C'],
            ['place' => '124', 'code' => '00-72238-0000', 'name' => 'PP-FARBE VIOLETT 466413', 'description' => 'PAN 263C'],
            ['place' => '136', 'code' => '00-72245-0000', 'name' => 'PP-FARBE ROSA 460416', 'description' => 'PAN 234C'],
            ['place' => '136', 'code' => '00-72246-0000', 'name' => 'PP-FARBE ORANGE 457416', 'description' => 'PAN 489C'],
            ['place' => '136', 'code' => '00-72247-0000', 'name' => 'PP-FARBE VIOLETT 454416', 'description' => 'PAN 261C'],
            ['place' => '136', 'code' => '00-72248-0000', 'name' => 'PP-FARBE GRÜN 461416', 'description' => 'PAN 7480C'],
            ['place' => '136', 'code' => '00-72249-0000', 'name' => 'PP-FARBE TÜRKIS 463416', 'description' => 'PAN 326C'],
            ['place' => '136', 'code' => '00-72250-0000', 'name' => 'PP-FARBE BLAU 444416', 'description' => 'PAN 296C'],
            ['place' => '136', 'code' => '00-72251-0000', 'name' => 'PP-FARBE TÜRKIS 459416', 'description' => 'PAN 7468C'],
            ['place' => '136', 'code' => '00-72252-0000', 'name' => 'PP-FARBE TÜRKIS 610416', 'description' => 'PAN 331C'],
            ['place' => '143', 'code' => '00-72253-0000', 'name' => 'PP-FARBE greige 101692', 'description' => null],
            ['place' => '142', 'code' => '00-72254-0000', 'name' => 'UNI-FARBE ORANGE 2606/P165C', 'description' => 'PAN 165C'],
            ['place' => '142', 'code' => '00-72305-0000', 'name' => 'PS-FARBE ROT 334416', 'description' => 'PAN warmred C'],
            ['place' => '142', 'code' => '00-72307-0000', 'name' => 'PS-FARBE BLAU 336416', 'description' => 'PAN 3125C'],
            ['place' => '143', 'code' => '00-72311-0000', 'name' => 'PS-FARBE BLAU 584416', 'description' => 'PAN 287C'],
            ['place' => '142', 'code' => '00-72312-0000', 'name' => 'PE-FARBE SCHWARZ 8022', 'description' => 'PAN 426C'],
            ['place' => '126', 'code' => '00-72313-0000', 'name' => 'PS-FARBE GRÜN 442418 /361C', 'description' => 'PAN 361C'],
            ['place' => '125', 'code' => '00-72314-0000', 'name' => 'PS-FARBE ORANGE 493418 /1585C', 'description' => 'PAN 1585C'],
            ['place' => '142', 'code' => '00-72316-0000', 'name' => 'PP-FARBE WEISS 820', 'description' => null],
            ['place' => '126', 'code' => '00-72900-0000', 'name' => 'Gleitmittel 100%', 'description' => null],
            ['place' => '101', 'code' => '00-72990-0000', 'name' => 'POLYBATCH VLA 620 HT NAT', 'description' => null],
            ['place' => '101', 'code' => '00-72991-0000', 'name' => 'Omyalene Kreidezusatz', 'description' => null],
        ];

 foreach ($records as $record) {
            DB::table('colors')->updateOrInsert(
                [
                    'code' => $record['code'],
                    'place' => $record['place'],
                ],
                [
                    'name' => $record['name'],
                    'description' => $record['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
