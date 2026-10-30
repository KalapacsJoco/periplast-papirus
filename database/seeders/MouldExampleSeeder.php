<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MouldExampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $records = [
            [
                        "number" => "00-82028-0000",
                        "place" => "201",
                        "name" => "Snapspule HOS",
                        "description" => null
            ],
            [
                        "number" => "00-82034-0000",
                        "place" => "201",
                        "name" => "Snapspule 75237",
                        "description" => null
            ],
            [
                        "number" => "00-82037-0000",
                        "place" => "201",
                        "name" => "Z Snapspule 7SB2",
                        "description" => null
            ],
            [
                        "number" => "00-81059-0000",
                        "place" => "201",
                        "name" => "Nahgarnspule T58",
                        "description" => null
            ],
            [
                        "number" => "00-82007-0000",
                        "place" => "201",
                        "name" => "Snapspule H010-!I",
                        "description" => null
            ],
            [
                        "number" => "00-82009-0000",
                        "place" => "201",
                        "name" => "Snapspule A300",
                        "description" => null
            ],
            [
                        "number" => "00-82004-0000",
                        "place" => "201",
                        "name" => "Snapspule 751004",
                        "description" => null
            ],
            [
                        "number" => "00-82035-0000",
                        "place" => "201",
                        "name" => "Snapspule 7584",
                        "description" => null
            ],
            [
                        "number" => "00-82047-0000",
                        "place" => "201",
                        "name" => "Snapspule H4",
                        "description" => null
            ],
            [
                        "number" => "00-84180-0000",
                        "place" => "201",
                        "name" => "Riemenrad 1",
                        "description" => null
            ],
            [
                        "number" => "00-82067-0000",
                        "place" => "204",
                        "name" => "Snapspule KR18",
                        "description" => null
            ],
            [
                        "number" => "00-82032-0000",
                        "place" => "204",
                        "name" => "Snapspule HO10- inaktiv (DUse fehit)",
                        "description" => null
            ],
            [
                        "number" => "00-83004-0000",
                        "place" => "204",
                        "name" => "Konische Hillse CW120 ’",
                        "description" => null
            ],
            [
                        "number" => "00-86076-0000",
                        "place" => "204",
                        "name" => "MSOCU Unterteil",
                        "description" => null
            ],
            [
                        "number" => "00-86070-0000",
                        "place" => "204",
                        "name" => "M100CU Unterteil nyu =",
                        "description" => null
            ],
            [
                        "number" => "00-86084-0000",
                        "place" => "204",
                        "name" => "Magazin DDHC Unterteil 3+2 oe",
                        "description" => null
            ],
            [
                        "number" => "00-82054-0000",
                        "place" => "204",
                        "name" => "Mini-Fufspule KSM(4) a ,",
                        "description" => null
            ],
            [
                        "number" => "00-86082-0000",
                        "place" => "207",
                        "name" => "K402AGGehause Kassette",
                        "description" => null
            ],
            [
                        "number" => "00-86090-0000",
                        "place" => "207",
                        "name" => "M1004DG3/M1022DG4 5 eke oF",
                        "description" => null
            ],
            [
                        "number" => "00-82023-0000",
                        "place" => "207",
                        "name" => "Snapspule A950 oh",
                        "description" => null
            ],
            [
                        "number" => "00-82025-0000",
                        "place" => "207",
                        "name" => "Snapspule AS004 [",
                        "description" => null
            ],
            [
                        "number" => "00-82045-0000",
                        "place" => "211",
                        "name" => "Snapspule KR60M/KR61M 1 +",
                        "description" => null
            ],
            [
                        "number" => "00-82053-0000",
                        "place" => "211",
                        "name" => "Snapspule KR12/KR121/H60 a m3",
                        "description" => null
            ],
            [
                        "number" => "00-82042-0000",
                        "place" => "212",
                        "name" => "Snapspule HOS0/HOSOH2 [",
                        "description" => null
            ],
            [
                        "number" => "00-82049-0000",
                        "place" => "212",
                        "name" => "Snapspule HO20 Ca",
                        "description" => null
            ],
            [
                        "number" => "00-82051-0000",
                        "place" => "212",
                        "name" => "Snapspule H40T/H40ABS",
                        "description" => null
            ],
            [
                        "number" => "00-81051-0000",
                        "place" => "212",
                        "name" => "Snapspule KR11/KR11T a ee",
                        "description" => null
            ],
            [
                        "number" => "00-82040-0000",
                        "place" => "212",
                        "name" => "KR16/KR16R/KR165/KR17 en",
                        "description" => null
            ],
            [
                        "number" => "00-82068-0000",
                        "place" => "212",
                        "name" => "SnapspulePWSOOR",
                        "description" => null
            ],
            [
                        "number" => "00-81041-0000",
                        "place" => "212",
                        "name" => "2ylindrische Halse ZH63 a=",
                        "description" => null
            ],
            [
                        "number" => "00-84024-0000",
                        "place" => "423",
                        "name" => "Gleitstein DF164013 A/B/C",
                        "description" => null
            ],
            [
                        "number" => "00-86011-0000",
                        "place" => "423",
                        "name" => "Gehduse Schlossteil $1001",
                        "description" => null
            ],
            [
                        "number" => "00-86012-0000",
                        "place" => "423",
                        "name" => "Schliesskern 851002",
                        "description" => null
            ],
            [
                        "number" => "00-86013-0000",
                        "place" => "423",
                        "name" => "Made Schlossteil $5103",
                        "description" => null
            ],
            [
                        "number" => "00-86050-0000",
                        "place" => "423",
                        "name" => "Haken KE6530CB",
                        "description" => null
            ],
            [
                        "number" => "00-81007-0000",
                        "place" => "423",
                        "name" => "L250BN/BNF/BNF2",
                        "description" => null
            ],
            [
                        "number" => "00-81024-0000",
                        "place" => "423",
                        "name" => "L150/1200/L2006A/6M",
                        "description" => null
            ],
            [
                        "number" => "00-81037-0000",
                        "place" => "424",
                        "name" => "LS0/L100/N/PN/PNF",
                        "description" => null
            ],
            [
                        "number" => "00-81048-0000",
                        "place" => "424",
                        "name" => "Létdrahtspule L300/G/GF/GS",
                        "description" => null
            ],
            [
                        "number" => "00-86048-0000",
                        "place" => "424",
                        "name" => "Einsatz ZM2000KSM",
                        "description" => null
            ],
            [
                        "number" => "00-81069-0000",
                        "place" => "424",
                        "name" => "L1400AS",
                        "description" => null
            ],
            [
                        "number" => "00-82048-0000",
                        "place" => "425",
                        "name" => "Snapspule H4P SES",
                        "description" => null
            ],
            [
                        "number" => "80-86409-0000",
                        "place" => "426",
                        "name" => "Golftee 1+2 Test",
                        "description" => null
            ],
            [
                        "number" => "00-82030-0000",
                        "place" => "421",
                        "name" => "Snapspule KS1(2)",
                        "description" => null
            ],
            [
                        "number" => "00-82064-0000",
                        "place" => "421",
                        "name" => "Snapspule KS1K(2)",
                        "description" => null
            ],
            [
                        "number" => "00-81002-0000",
                        "place" => "421",
                        "name" => "FuBspule KS2",
                        "description" => null
            ],
            [
                        "number" => "00-81613-0000",
                        "place" => "315",
                        "name" => "Box BSN ae",
                        "description" => null
            ],
            [
                        "number" => "00-81614-0000",
                        "place" => "315",
                        "name" => "Box MILO",
                        "description" => null
            ],
            [
                        "number" => "00-82003-0000",
                        "place" => "201",
                        "name" => "Snapspule 751022",
                        "description" => null
            ],
            [
                        "number" => "00-82004-0000",
                        "place" => "203",
                        "name" => "Snapspule 751004",
                        "description" => null
            ],
            [
                        "number" => "00-82005-0000",
                        "place" => "216",
                        "name" => "Snapspule PWS00",
                        "description" => null
            ],
            [
                        "number" => "00-82007-0000",
                        "place" => "202",
                        "name" => "Snapspule H010-1",
                        "description" => null
            ],
            [
                        "number" => "00-82009-0000",
                        "place" => "202",
                        "name" => "Snapspule A300",
                        "description" => null
            ],
            [
                        "number" => "00-82010-0000",
                        "place" => "203",
                        "name" => "Shapspule KR60",
                        "description" => null
            ],
            [
                        "number" => "00-82011-0000",
                        "place" => "216",
                        "name" => "Snapspule A200/AS00",
                        "description" => null
            ],
            [
                        "number" => "00-82012-0000",
                        "place" => "211",
                        "name" => "Snapspule A900 “",
                        "description" => null
            ],
            [
                        "number" => "00-82016-0000",
                        "place" => "421",
                        "name" => "Mini-FuRspule KSM/KSMmini",
                        "description" => null
            ],
            [
                        "number" => "00-82019-0000",
                        "place" => "213",
                        "name" => "Snapspule PW200",
                        "description" => null
            ],
            [
                        "number" => "00-82022-0000",
                        "place" => "215",
                        "name" => "Snapspule D300",
                        "description" => null
            ],
            [
                        "number" => "00-82023-0000",
                        "place" => "211",
                        "name" => "Snapspule A950",
                        "description" => null
            ],
            [
                        "number" => "00-82024-0000",
                        "place" => "212",
                        "name" => "Snapspule H45",
                        "description" => null
            ],
            [
                        "number" => "00-82025-0000",
                        "place" => "211",
                        "name" => "Snapspule A5004",
                        "description" => null
            ],
            [
                        "number" => "00-82026-0000",
                        "place" => "202",
                        "name" => "Snapspule Flansch HD010 ]",
                        "description" => null
            ],
            [
                        "number" => "00-82029-0000",
                        "place" => "216",
                        "name" => "Snapspule PW100",
                        "description" => null
            ],
            [
                        "number" => "00-82030-0000",
                        "place" => "421",
                        "name" => "Snapspule KS1(2) 4",
                        "description" => null
            ],
            [
                        "number" => "00-82031-0000",
                        "place" => "403",
                        "name" => "Snapspule KS1/KS1K",
                        "description" => null
            ],
            [
                        "number" => "00-82032-0000",
                        "place" => "204",
                        "name" => "Snapspule H010-1 inaktiv (Duse fehit)",
                        "description" => null
            ],
            [
                        "number" => "00-82033-0000",
                        "place" => "216",
                        "name" => "Snapspule 751002 =",
                        "description" => null
            ],
            [
                        "number" => "00-82035-0000",
                        "place" => "203",
                        "name" => "Snapspule 7884",
                        "description" => null
            ],
            [
                        "number" => "00-82040-0000",
                        "place" => "213",
                        "name" => "KR16/KR16R/KR165/KR17 =m",
                        "description" => null
            ],
            [
                        "number" => "00-82045-0000",
                        "place" => "211",
                        "name" => "Snapspule KR60M/KR61M",
                        "description" => null
            ],
            [
                        "number" => "00-82047-0000",
                        "place" => "203",
                        "name" => "Snapspule H4 “a",
                        "description" => null
            ],
            [
                        "number" => "00-82050-0000",
                        "place" => "213",
                        "name" => "Snapspule HO20R / HO20S",
                        "description" => null
            ],
            [
                        "number" => "00-82051-0000",
                        "place" => "212",
                        "name" => "Snapspule H40T/H40ABS ;",
                        "description" => null
            ],
            [
                        "number" => "00-82053-0000",
                        "place" => "211",
                        "name" => "Snapspule KR12/KR121/H60 p.",
                        "description" => null
            ],
            [
                        "number" => "00-82054-0000",
                        "place" => "207",
                        "name" => "Mini-Fuspule KSM(4) Z",
                        "description" => null
            ],
            [
                        "number" => "00-82062-0000",
                        "place" => "212",
                        "name" => "Snapspule KR100 ess Gr",
                        "description" => null
            ],
            [
                        "number" => "00-83002-0000",
                        "place" => "314",
                        "name" => "Konische Hulse C100/C100N",
                        "description" => null
            ],
            [
                        "number" => "00-83004-0000",
                        "place" => "205",
                        "name" => "Konische Hulse CW120 [ ee",
                        "description" => null
            ],
            [
                        "number" => "00-86092-0000",
                        "place" => "311",
                        "name" => "M1004DF3+M1022DF4 ; ae",
                        "description" => null
            ],
            [
                        "number" => "00-86093-0000",
                        "place" => "307",
                        "name" => "MSKG Gehause",
                        "description" => null
            ],
            [
                        "number" => "00-86094-0000",
                        "place" => "312",
                        "name" => "MSKE Einsatz 142",
                        "description" => null
            ],
            [
                        "number" => "00-86095-0000",
                        "place" => "312",
                        "name" => "MSKD Deckel",
                        "description" => null
            ],
            [
                        "number" => "00-86096-0000",
                        "place" => "301",
                        "name" => "MK2AG/MK3AG/MK4AG Gehause i,",
                        "description" => null
            ],
            [
                        "number" => "00-86096-0000",
                        "place" => "302",
                        "name" => "MK2AG/MK3AG/MK4AG Gehause",
                        "description" => null
            ],
            [
                        "number" => "00-86099-0000",
                        "place" => "302",
                        "name" => "MK2AF Federhalter",
                        "description" => null
            ],
            [
                        "number" => "00-86100-0000",
                        "place" => "302",
                        "name" => "MK3AF Federhalter a",
                        "description" => null
            ],
            [
                        "number" => "00-86203-0000",
                        "place" => "304",
                        "name" => "Delta-Xpress KNEE 25 ia",
                        "description" => null
            ],
            [
                        "number" => "00-86204-0000",
                        "place" => "316",
                        "name" => "RHIF \"SR\" . =",
                        "description" => null
            ],
            [
                        "number" => "00-97015-0000",
                        "place" => "412",
                        "name" => "Handgriff 432806/432807",
                        "description" => null
            ],
            [
                        "number" => "00-87017-0000",
                        "place" => "315",
                        "name" => "Sagegriff GM2102/2202/2302 EN",
                        "description" => null
            ],
            [
                        "number" => "00-87027-0000",
                        "place" => "412",
                        "name" => "Diibel A10 4 ae j",
                        "description" => null
            ],
            [
                        "number" => "00-83012-0000",
                        "place" => "314",
                        "name" => "Konische Hillse KON",
                        "description" => null
            ],
            [
                        "number" => "00-83014-0000",
                        "place" => "301",
                        "name" => "Konische Hilse C114",
                        "description" => null
            ],
            [
                        "number" => "00-84015-0000",
                        "place" => "412",
                        "name" => "NST 5585005 / 5582032 =",
                        "description" => null
            ],
            [
                        "number" => "00-84015-0000",
                        "place" => "423",
                        "name" => "NST 55B5005 / 5582032",
                        "description" => null
            ],
            [
                        "number" => "00-84018-0000",
                        "place" => "313",
                        "name" => "Modul-Unterteil 5460001003",
                        "description" => null
            ],
            [
                        "number" => "00-84019-0000",
                        "place" => "313",
                        "name" => "Modul-Deckel 5460002003",
                        "description" => null
            ],
            [
                        "number" => "00-84020-0000",
                        "place" => "313",
                        "name" => "Modul-Abstandselement",
                        "description" => null
            ],
            [
                        "number" => "00-84021-0000",
                        "place" => "313",
                        "name" => "Modul-Einsatz 54600040034",
                        "description" => null
            ],
            [
                        "number" => "00-84024-0000",
                        "place" => "423",
                        "name" => "Gleitstein DF 164013 A/8/C",
                        "description" => null
            ],
            [
                        "number" => "00-84025-0000",
                        "place" => "411",
                        "name" => "DF16A802 / DF24801 /-802",
                        "description" => null
            ],
            [
                        "number" => "00-84063-0000",
                        "place" => "307",
                        "name" => "Haube fiir Motor HA100 ]",
                        "description" => null
            ],
            [
                        "number" => "00-84065-0000",
                        "place" => "205",
                        "name" => "055000061/055000062",
                        "description" => null
            ],
            [
                        "number" => "00-84089-0000",
                        "place" => "412",
                        "name" => "051000199/200/201/202",
                        "description" => null
            ],
            [
                        "number" => "00-84090-0000",
                        "place" => "423",
                        "name" => "Fihrung 051000203 cm.",
                        "description" => null
            ],
            [
                        "number" => "00-84094-0000",
                        "place" => "412",
                        "name" => "Greiferbacke O11A1205 i oid",
                        "description" => null
            ],
            [
                        "number" => "00-84118-0000",
                        "place" => "423",
                        "name" => "Spule mit Snapring HERA2 ai",
                        "description" => null
            ],
            [
                        "number" => "00-84149-0000",
                        "place" => "423",
                        "name" => "’tift D2x.., [",
                        "description" => null
            ],
            [
                        "number" => "00-84180-0000",
                        "place" => "204",
                        "name" => "Riemenrad1 [",
                        "description" => null
            ],
            [
                        "number" => "00-86003-0000",
                        "place" => "315",
                        "name" => "Boden SVK2B",
                        "description" => null
            ],
            [
                        "number" => "00-86004-0000",
                        "place" => "315",
                        "name" => "Deckel SVK2D iy i. ]",
                        "description" => null
            ],
            [
                        "number" => "00-86050-0000",
                        "place" => "423",
                        "name" => "Haken KE6530CB [",
                        "description" => null
            ],
            [
                        "number" => "00-86070-0000",
                        "place" => "206",
                        "name" => "M100CU Unterteil ; an",
                        "description" => null
            ],
            [
                        "number" => "00-86071-0000",
                        "place" => "312",
                        "name" => "MCDDeckel 7",
                        "description" => null
            ],
            [
                        "number" => "00-86072-0000",
                        "place" => "311",
                        "name" => "M100CF Federhalter Ea",
                        "description" => null
            ],
            [
                        "number" => "00-86074-0000",
                        "place" => "206",
                        "name" => "M200CU+MSOOCU Unterteil",
                        "description" => null
            ],
            [
                        "number" => "00-86077-0000",
                        "place" => "311",
                        "name" => "MSOCF Federhalter",
                        "description" => null
            ],
            [
                        "number" => "00-86078-0000",
                        "place" => "312",
                        "name" => "M50-500CB Blindstopfen rae",
                        "description" => null
            ],
            [
                        "number" => "00-86079-0000",
                        "place" => "311",
                        "name" => "M1000CB/M1000CF ee a.",
                        "description" => null
            ],
            [
                        "number" => "00-86082-0000",
                        "place" => "207",
                        "name" => "K402AG Gehause Kassette ns",
                        "description" => null
            ],
            [
                        "number" => "00-81001-0000",
                        "place" => "422",
                        "name" => "FuRspule KS2A/KS3/KS3A Sete Mealesyséeek \"",
                        "description" => null
            ],
            [
                        "number" => "00-81002-0000",
                        "place" => "422",
                        "name" => "FuRspule KS2",
                        "description" => null
            ],
            [
                        "number" => "00-81004-0000",
                        "place" => "416",
                        "name" => "Létdrahtspule L110",
                        "description" => null
            ],
            [
                        "number" => "00-81005-0000",
                        "place" => "304",
                        "name" => "S400/S500/S600/S700",
                        "description" => null
            ],
            [
                        "number" => "00-81007-0000",
                        "place" => "424",
                        "name" => "L2S50BN/BNF/BNF2",
                        "description" => null
            ],
            [
                        "number" => "00-81008-0000",
                        "place" => "416",
                        "name" => "L250/L250A/L250TK3",
                        "description" => null
            ],
            [
                        "number" => "00-81011-0000",
                        "place" => "413",
                        "name" => "Drahtspule LS00S",
                        "description" => null
            ],
            [
                        "number" => "00-81012-0000",
                        "place" => "403",
                        "name" => "FuBspule FSO",
                        "description" => null
            ],
            [
                        "number" => "00-81013-0000",
                        "place" => "405",
                        "name" => "FuRspule FS1/X4",
                        "description" => null
            ],
            [
                        "number" => "00-81014-0000",
                        "place" => "404",
                        "name" => "FuRspule FS2",
                        "description" => null
            ],
            [
                        "number" => "00-81015-0000",
                        "place" => "407",
                        "name" => "FuRspule FS6 j",
                        "description" => null
            ],
            [
                        "number" => "00-81016-0000",
                        "place" => "411",
                        "name" => "FS6 Ring",
                        "description" => null
            ],
            [
                        "number" => "00-81017-0000",
                        "place" => "303",
                        "name" => "FuBspule X1",
                        "description" => null
            ],
            [
                        "number" => "00-81018-0000",
                        "place" => "303",
                        "name" => "Fufspule X2 7",
                        "description" => null
            ],
            [
                        "number" => "00-81019-0000",
                        "place" => "404",
                        "name" => "Fu&spule FS4",
                        "description" => null
            ],
            [
                        "number" => "00-81020-0000",
                        "place" => "403",
                        "name" => "Drahtspule L1000S",
                        "description" => null
            ],
            [
                        "number" => "00-81023-0000",
                        "place" => "414",
                        "name" => "Létdrahtspule L1000 a sy =",
                        "description" => null
            ],
            [
                        "number" => "00-81024-0000",
                        "place" => "424",
                        "name" => "L150/L200/\\200GA/6M = t~*é",
                        "description" => null
            ],
            [
                        "number" => "00-81025-0000",
                        "place" => "414",
                        "name" => "Bandspule $100/-P/-BOB/-J ‘i",
                        "description" => null
            ],
            [
                        "number" => "00-81028-0000",
                        "place" => "412",
                        "name" => "Q8A08A1/B1 is",
                        "description" => null
            ],
            [
                        "number" => "00-81030-0000",
                        "place" => "411",
                        "name" => "L1500/2000/2500/3500 PC 7",
                        "description" => null
            ],
            [
                        "number" => "00-81032-0000",
                        "place" => "416",
                        "name" => "Létdrahtspule LSSO",
                        "description" => null
            ],
            [
                        "number" => "00-81033-0000",
                        "place" => "404",
                        "name" => "Fuspule FSS",
                        "description" => null
            ],
            [
                        "number" => "00-81035-0000",
                        "place" => "416",
                        "name" => "Drahtspule L600/L600R/1900 Vez",
                        "description" => null
            ],
            [
                        "number" => "00-81036-0000",
                        "place" => "413",
                        "name" => "Létdrahtspule LS00R/L1000R a .",
                        "description" => null
            ],
            [
                        "number" => "00-81037-0000",
                        "place" => "424",
                        "name" => "LSO/L100/N/PN/PNF [24 an",
                        "description" => null
            ],
            [
                        "number" => "00-81046-0000",
                        "place" => "414",
                        "name" => "Bandspule SP2N al",
                        "description" => null
            ],
            [
                        "number" => "00-81061-0000",
                        "place" => "214",
                        "name" => "Zylindrische Hilse+Flansch ma)",
                        "description" => null
            ],
            [
                        "number" => "00-81063-0000",
                        "place" => "212",
                        "name" => "Nahgarnspule KR10 a",
                        "description" => null
            ],
            [
                        "number" => "00-81065-0000",
                        "place" => "402",
                        "name" => "Innenring 4059 52,5x69x1,5 =",
                        "description" => null
            ],
            [
                        "number" => "00-81066-0000",
                        "place" => "405",
                        "name" => "FuBspule FSO per",
                        "description" => null
            ],
            [
                        "number" => "00-81068-0000",
                        "place" => "413",
                        "name" => "LSO0V18/L1000V18",
                        "description" => null
            ],
            [
                        "number" => "00-81109-0000",
                        "place" => "306",
                        "name" => "Puck 1\"CHARTRES\" Becher =",
                        "description" => null
            ],
            [
                        "number" => "00-82011-0000",
                        "place" => "216",
                        "name" => "Snapspule A200/A500",
                        "description" => null
            ],
            [
                        "number" => "00-82033-0000",
                        "place" => "216",
                        "name" => "Snapspule 751002",
                        "description" => null
            ],
            [
                        "number" => "00-83014-0000",
                        "place" => "216",
                        "name" => "Konische Hillse C114",
                        "description" => null
            ],
            [
                        "number" => "00-86096-0000",
                        "place" => "216",
                        "name" => "MK2AG/MK3AG/MK4AG Gehause",
                        "description" => null
            ],
            [
                        "number" => "00-86098-0000",
                        "place" => "301",
                        "name" => "MKAR Rahmenplatte (2203 Gelb)",
                        "description" => null
            ],
            [
                        "number" => "00-86096-0000",
                        "place" => "301",
                        "name" => "MK2AG/MK3AG/MK4AG Gehause",
                        "description" => null
            ],
            [
                        "number" => "00-86099-0000",
                        "place" => "301",
                        "name" => "MK2AF Federhalter",
                        "description" => null
            ],
            [
                        "number" => "00-86100-0000",
                        "place" => "301",
                        "name" => "MK3AF Federhalter [",
                        "description" => null
            ],
            [
                        "number" => "00-81017-0000",
                        "place" => "303",
                        "name" => "FuBspule X1 es.",
                        "description" => null
            ],
            [
                        "number" => "00-81018-0000",
                        "place" => "303",
                        "name" => "FuRspule x2 “a =",
                        "description" => null
            ],
            [
                        "number" => "00-81005-0000",
                        "place" => "303",
                        "name" => "S400/S500/S600/S700 a a",
                        "description" => null
            ],
            [
                        "number" => "00-86203-0000",
                        "place" => "303",
                        "name" => "Delta-Xpress KNEE ae Se - e =",
                        "description" => null
            ],
            [
                        "number" => "00-87100-0000",
                        "place" => "303",
                        "name" => "Puck M Becher Plan 2 [ =",
                        "description" => null
            ],
            [
                        "number" => "00-87102-0000",
                        "place" => "303",
                        "name" => "Puck XL2 Becher ? ie",
                        "description" => null
            ],
            [
                        "number" => "00-87103-0000",
                        "place" => "303",
                        "name" => "Puck XL2 Einsatz aa",
                        "description" => null
            ],
            [
                        "number" => "00-87104-0000",
                        "place" => "303",
                        "name" => "Puck XL2 Wabeneinsatz",
                        "description" => null
            ],
            [
                        "number" => "00-87106-0000",
                        "place" => "303",
                        "name" => "Puck\"GREEN\" Becher =",
                        "description" => null
            ],
            [
                        "number" => "00-86072-0000",
                        "place" => "303",
                        "name" => "M100CFFederhalter ;",
                        "description" => null
            ],
            [
                        "number" => "00-86075-0000",
                        "place" => "303",
                        "name" => "M200CF+MSOOCF Federhalter Tar ist",
                        "description" => null
            ],
            [
                        "number" => "00-86079-0000",
                        "place" => "303",
                        "name" => "M1000CB/M1000CF g",
                        "description" => null
            ],
            [
                        "number" => "00-86085-0000",
                        "place" => "303",
                        "name" => "Magazin DDHC Federh. 3+2",
                        "description" => null
            ],
            [
                        "number" => "00-86071-0000",
                        "place" => "303",
                        "name" => "MCD Deckel ee rie 7",
                        "description" => null
            ],
            [
                        "number" => "00-86073-0000",
                        "place" => "303",
                        "name" => "MSOCE-MSOOCE+MDDHCE af",
                        "description" => null
            ],
            [
                        "number" => "00-86078-0000",
                        "place" => "303",
                        "name" => "MS0-SOOCBBlindstopfen",
                        "description" => null
            ],
            [
                        "number" => "00-86088-0000",
                        "place" => "303",
                        "name" => "Unterteiler lang+kurz “a",
                        "description" => null
            ],
            [
                        "number" => "00-86091-0000",
                        "place" => "303",
                        "name" => "'SPWKZ MDD Deckel on",
                        "description" => null
            ],
            [
                        "number" => "00-86094-0000",
                        "place" => "303",
                        "name" => "MSKE Einsatz 1+2 a <=",
                        "description" => null
            ],
            [
                        "number" => "00-86206-0000",
                        "place" => "316",
                        "name" => "RHIF \"MR\"",
                        "description" => null
            ],
            [
                        "number" => "00-86207-0000",
                        "place" => "316",
                        "name" => "RHIF \"ML\"",
                        "description" => null
            ],
            [
                        "number" => "00-86208-0000",
                        "place" => "316",
                        "name" => "RHIF \"LR\"",
                        "description" => null
            ],
            [
                        "number" => "00-86209-0000",
                        "place" => "316",
                        "name" => "RHIF \"LL\"",
                        "description" => null
            ],
            [
                        "number" => "00-86087-0000",
                        "place" => "401",
                        "name" => "Schublade DC1",
                        "description" => null
            ],
            [
                        "number" => "00-81065-0000",
                        "place" => "401",
                        "name" => "Innenring 4059 52,5x69x1,5",
                        "description" => null
            ],
            [
                        "number" => "00-87021-0000",
                        "place" => "401",
                        "name" => "Gehrungslade ZF30010",
                        "description" => null
            ],
            [
                        "number" => "00-81012-0000",
                        "place" => "401",
                        "name" => "FuBspule FSO",
                        "description" => null
            ],
            [
                        "number" => "00-81014-0000",
                        "place" => "403",
                        "name" => "FuRspule FS2",
                        "description" => null
            ],
            [
                        "number" => "00-81019-0000",
                        "place" => "404",
                        "name" => "Fu&spule FS4 = L",
                        "description" => null
            ],
            [
                        "number" => "00-81033-0000",
                        "place" => "404",
                        "name" => "FuBspule FSS me.",
                        "description" => null
            ],
            [
                        "number" => "00-81066-0000",
                        "place" => "404",
                        "name" => "FuRspule FSO",
                        "description" => null
            ],
            [
                        "number" => "00-86302-0000",
                        "place" => "404",
                        "name" => "EPH Haube ea",
                        "description" => null
            ],
            [
                        "number" => "00-84044-0000",
                        "place" => "404",
                        "name" => "Einsatzteile f. Motor HE200",
                        "description" => null
            ],
            [
                        "number" => "00-81028-0000",
                        "place" => "404",
                        "name" => "Q8A08A1/B1 aaa 7",
                        "description" => null
            ],
            [
                        "number" => "00-84089-0000",
                        "place" => "404",
                        "name" => "051000199/200/201/202 [",
                        "description" => null
            ],
            [
                        "number" => "00-84127-0000",
                        "place" => "404",
                        "name" => "Halteteil 051000274",
                        "description" => null
            ],
            [
                        "number" => "00-87012-0000",
                        "place" => "404",
                        "name" => "DiibelAG",
                        "description" => null
            ],
            [
                        "number" => "00-87015-0000",
                        "place" => "412",
                        "name" => "Handgriff 432806/432807 [",
                        "description" => null
            ],
            [
                        "number" => "00-87027-0000",
                        "place" => "412",
                        "name" => "Dilbel A10. =",
                        "description" => null
            ],
            [
                        "number" => "00-87030-0000",
                        "place" => "412",
                        "name" => "Dilbel AS",
                        "description" => null
            ],
            [
                        "number" => "00-84015-0000",
                        "place" => "412",
                        "name" => "NST 5585005 / 5582032",
                        "description" => null
            ],
            [
                        "number" => "00-81011-0000",
                        "place" => "412",
                        "name" => "Drahtspule LS00S",
                        "description" => null
            ],
            [
                        "number" => "00-81067-0000",
                        "place" => "412",
                        "name" => "Drahtspule L250V18",
                        "description" => null
            ],
            [
                        "number" => "00-81023-0000",
                        "place" => "412",
                        "name" => "Lotdrahtspule L1000",
                        "description" => null
            ]
];

        foreach ($records as $record) {
            DB::table('moulds')->updateOrInsert(
                [
                    'number' => $record['number'],
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
