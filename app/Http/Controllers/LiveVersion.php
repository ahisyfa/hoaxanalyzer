<?php

namespace App\Http\Controllers;

use App\TfIdf;
use App\Df;
use App\PanjangVektor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class LiveVersion extends Controller
{
    public static function prepare(){
        echo "LiveVersion :: Prepare for live version..\n";

        // 1. Update table tdm menjadi data latih semua
        DB::table('tdms')->update(['test_data'=>false]);

        // 2. Lakukan seleksi fitur
        Df::generate();
        Df::updateFeatureSelectionByIdf();

        // 3. Lakukan perhitungan bobot tf.idf
        TfIdf::generate();

        // 4. Lakukan perhitungan panjang vektor
        PanjangVektor::generate();

        // 5. Siap dihidangkan
        echo "LiveVersion :: Ready to shake the world!\n";
    }
}
