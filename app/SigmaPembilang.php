<?php

namespace App;

use App\TfIdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class SigmaPembilang extends Model
{
    public $incrementing = false;
    public $timestamps = false;


    public static function generate($dokumen_uji){
        echo "SigmaPembilang::generate(dokumen_uji = {$dokumen_uji}) \r\n";

        SigmaPembilang::truncate();

        $tf_idfs = TfIdf::where('document', $dokumen_uji)->get();

        foreach ($tf_idfs as $tf_idf) {
            $tf_idf_inners = TfIdf::where('term', $tf_idf->term )
                ->whereNotIn('document', [$dokumen_uji])
                ->get();

            foreach ($tf_idf_inners as $item){
                $sp           = new SigmaPembilang();
                $sp->term     = $tf_idf->term;
                $sp->document = $item->document;
                $sp->nilai    = $tf_idf->tf_idf * $item->tf_idf;

                $sp->save();

                echo "SigmaPembilang::generate(dok_uji {$dokumen_uji}) -> Proses {$tf_idf->term} : {$item->document} \r\n";
            }
        }
    }

    public static function getSigmaPembilang($id_dokumen){
        $sigma_pembilang = DB::table('sigma_pembilangs')
                            ->select(DB::raw('SUM(nilai) AS nilai'))
                            ->where('document', $id_dokumen)
                            ->first();

        return $sigma_pembilang->nilai == null ? 0 : $sigma_pembilang->nilai;
    }
}
