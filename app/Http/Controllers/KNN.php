<?php

namespace App\Http\Controllers;

use App\SigmaPembilang;
use App\Tdm;
use App\TfIdf;
use App\TfIdfUji;
use App\Df;
use App\KFold;
use App\PanjangVektor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class KNN extends Controller
{

    public function __construct(){
        date_default_timezone_set('Asia/Jakarta');
    }

    /**
     * Akses poi untuk memulai penelitin.
     *
     * c
     *
     * @param int $K banyaknya k pada KNNN
     */
    public function run($K = 5){
        echo "KNN::run({$K}) \r\n";
        for ($i = 1; $i <= 10; $i++){
            $this->doClassify($K, $i);
        }
    }

    /**
     * Helper klasifikasi
     *
     * @param int $K
     * @param int $fold
     * @return int
     */
    public function doClassify($K  = 5, $fold = 1){
        echo "KNN::doClassify(K={$K}, Fold={$fold}) \r\n";

        $time_start = microtime(true);

        $file_log_name = $fold . "_knn_" . date("Ymd_His") . '.txt';
        $log_handle    = fopen("hasil_penelitian\\" . $file_log_name, 'a') or die('Cannot open file:  '.$file_log_name);
        fwrite($log_handle, "Startting KNN Classification \r\n");
        fwrite($log_handle, "---------------------------- \r\n");
        fwrite($log_handle, "Waktu penelitian : ". date("Y-m-d H:i:s") . "\r\n");
        fwrite($log_handle, "Parameter  \r\n");
        fwrite($log_handle, "K         : ".  $K ." \r\n");
        fwrite($log_handle, "Data Fold : ".  $fold ." \r\n");
        fwrite($log_handle, "-----------------------------------------------------------  \r\n");
        fwrite($log_handle, "\r\nMulai :  \r\n");

        echo "Memulai Klasifikasi \n";
        echo "==========================\n";
        echo "K    : " . $K    ." \n";
        echo "Fold : " . $fold ." \n";

        // Eksekusi Klasifikasi
        $this->knnClassifier($log_handle, $K, $fold);

        // Catat waktu selesai eksekusi
        $time_end       = microtime(true);
        $execution_time = ($time_end - $time_start)/60;
        echo "Waktu eksekusi program: ". $execution_time ." menit\n";
        fwrite($log_handle, "Jam Selesai            : ". date("Y-m-d H:i:s") ."\r\n");
        fwrite($log_handle, "Waktu eksekusi program : ". round($execution_time, 2) ." menit \r\n");

        fclose($log_handle);
        return 0;

    }

    /**
     * Proses Klasifikasi KNN
     *
     * @param $log_handle
     * @param $K
     * @param $fold
     * @return bool
     */
    public function knnClassifier( & $log_handle, $K, $fold){
        echo "KNN::knnClassifier(K={$K}, Fold={$fold}) \r\n";

        // Confusion matrix
        // -------------------------
        //         HOAX     NONHOAX
        // HOAX    TP       FN
        // NONOAX  FP       TN

        // Precision = TP / ( TP + FP )
        // Recall    = TP / ( TP + FN )

        // Data Comfusin Matrix
        $TP = 0;
        $FN = 0;
        $FP = 0;
        $TN = 0;

        // KELAS
        $KELAS_AKTUAL   = "";
        $KELAS_PREDIKSI = "";

        $list_dokumen_uji    = $this->get_list_dokumen_uji($fold);
        $panjang_dokumen_uji = count($list_dokumen_uji);

        for ($i = 0; $i < $panjang_dokumen_uji; $i++ ){
            $dokumen_uji = $list_dokumen_uji[$i];

            // Tentukan kelas aktualnya
            $KELAS_AKTUAL = $dokumen_uji <= 300 ? 'HOAX' : 'NONHOAX';
            fwrite($log_handle, ($i+1) . ". Mengklasifikasi Doc {$dokumen_uji} [{$KELAS_AKTUAL}] \r\n");


            echo "knnClassifier :: Sedang memproses dokumen uji : {$dokumen_uji} \r\n";
            $this->seleksi_fitur($dokumen_uji, $list_dokumen_uji);

            // Melakukan perhitungan pembobotan tf.idf
            TfIdf::generate();

            // Hitung sigma Pembilang
            SigmaPembilang::generate($dokumen_uji);

            // Ambil data
            $panjang_vektor_uji = $this->get_panjang_vektor($dokumen_uji);

            // Array jarak
            $jarak_knn = array();

            // Proses klasifikasi KNN dimulai
            $list_dokumen_latih = $this->get_list_dokumen_latih($list_dokumen_uji);
            $counter = 1;
            foreach($list_dokumen_latih as $dokumen_latih){
                echo "   [{$counter}]. Handle uji: {$dokumen_uji} dengan latih :  {$dokumen_latih} \r\n";
                $counter++;

                // Perhitungan Cosinr simmilarity

                $sigma_pembilang      = SigmaPembilang::getSigmaPembilang($dokumen_latih);
                $panjang_vektor_latih = $this->get_panjang_vektor($dokumen_latih);

                $jarak = $sigma_pembilang / ($panjang_vektor_uji * $panjang_vektor_latih);

                $jarak_knn[$dokumen_latih] = $jarak;

            }

            // Sorting nilai dari besar ke kecil
            arsort($jarak_knn);

            // Votting
            $hoax     = 0;
            $non_hoax = 0;
            $banyak_vote = 0;

            fwrite($log_handle, "   Voting : \r\n");


            foreach($jarak_knn as $r_doc_id => $r_jarak ){
                if($banyak_vote == $K ){
                    break;
                }

                if ( $r_doc_id <= 300 ){
                    $hoax++;
                } else {
                    $non_hoax++;
                }

                fwrite($log_handle, "   - Doc {$r_doc_id} : $r_jarak \r\n");

                $banyak_vote++;
            }

            if ($hoax > $non_hoax ){
                $KELAS_PREDIKSI = "HOAX";
            } else {
                $KELAS_PREDIKSI = "NONHOAX";
            }

            echo "    HASILNYA  doc_id: {$dokumen_uji} [$KELAS_AKTUAL] adalah {$KELAS_PREDIKSI}. \n";
            fwrite($log_handle, "    HASILNYA  doc_id: {$dokumen_uji} [$KELAS_AKTUAL] adalah {$KELAS_PREDIKSI}. \r\n");


            // Hitung Confusision matrix
            if ( $KELAS_AKTUAL == "HOAX" && $KELAS_PREDIKSI == "HOAX" ){
                $TP++;
            } else if ( $KELAS_AKTUAL == "HOAX" && $KELAS_PREDIKSI == "NONHOAX" ){
                $FN++;
            } else if ( $KELAS_AKTUAL == "NONHOAX" && $KELAS_PREDIKSI == "HOAX" ){
                $FP++;
            } else if ( $KELAS_AKTUAL == "NONHOAX" && $KELAS_PREDIKSI == "NONHOAX" ){
                $TN++;
            }

        }


        fwrite($log_handle, "Selesai  \r\n\r\n");

        fwrite($log_handle, "-----------------------------------------------------------  \r\n");
        fwrite($log_handle, "TP : ". $TP ."\r\n");
        fwrite($log_handle, "FN : ". $FN ."\r\n");
        fwrite($log_handle, "FP : ". $FP ."\r\n");
        fwrite($log_handle, "TN : ". $TN ."\r\n");

        // Cetak ke log Confussion Matrix
        fwrite($log_handle, "\r\n");
        fwrite($log_handle, "Confussion Matrix \r\n");
        fwrite($log_handle, "-----------------------------------------------------------  \r\n");
        fwrite($log_handle, "         HOAX       NONHOAX \r\n");
        fwrite($log_handle, "HOAX    " . $TP . "    " . $FN . "\r\n");
        fwrite($log_handle, "NONHOAX " . $FP . "    " . $TN . "\r\n");
        fwrite($log_handle, "\r\n");

        if( $TP + $FN != 0){
            fwrite($log_handle, "Recall    = " .  $TP / ( $TP + $FN ) . "\r\n");
        }

        if( $TP + $FP != 0){
            fwrite($log_handle, "Precision = " .  $TP / ( $TP + $FP ) . "\r\n");
        }

        if ( ($TP + $FP + $FN + $TN ) != 0 ){
            fwrite($log_handle, "Akurasi   = " .  ( ($TP + $TN) / ( $TP + $FP + $FN + $TN) ) . "\r\n");
        }

        fwrite($log_handle, "-----------------------------------------------------------  \r\n");


        // Hitung Confusision Matrix
        echo "Confussion Matrix \n";
        echo "----------------- \n";
        echo "         HOAX       NONHOAX\n";
        echo "HOAX    " . $TP . "    " . $FN . "\n";
        echo "NONHOAX " . $FP . "    " . $TN . "\n";
        echo "\n";

        if( $TP + $FN != 0){
            echo  "Recall    =" .  $TP / ( $TP + $FN ) . "\n";
        }

        if( $TP + $FP != 0){
            echo  "Precision =" .  $TP / ( $TP + $FP ) . "\n";
        }

        return true;


    }

    /**
     * Digunakan untuk mengambil data uji berdasarkan
     * kfold yang dipilih.
     *
     * @param $fold kfold yang ada dipakai
     * @return array|null array_data_latih
     */
    public function get_list_dokumen_uji($fold){
        echo "KNN::get_list_dokumen_uji(Fold={$fold}) \r\n";

        $kfold     = new KFold();
        $row_kfold = $kfold->find($fold);

        // Ambil dokumen-dokumen yang menjadi data uji (test_data)
        $list_dokumen_uji = NULL;
        if ( $row_kfold != NULL ){
            $list_dokumen_uji = explode(",", $row_kfold->documents);
        } else {
            die("Data dokumen uji belum ada. periksa tabel kfold");
        }
        sort($list_dokumen_uji);

        return $list_dokumen_uji;
    }

    /**
     * Digunakan untuk melakukan proses seleksi fitur.
     * Pada proses ini, dat uji yang sedang diproses dianggap sebagai data latih
     * agar ikut terproses pada proses perhitungan idf.
     * Term-term yang ada pada dokumen uji yang sedang diproses tidak dikenakan selseksi fitur.
     *
     * @param $dokumen_uji
     * @param $list_dokumen_uji
     */
    public function seleksi_fitur( $dokumen_uji, $list_dokumen_uji ){
        echo "KNN::seleksi_fitur(dok_uji_prose = {$dokumen_uji}) \r\n";

        // Reset semua dokumen menjadi dokumen latih.
        DB::table('tdms')->update(array('test_data' => false));

        // Set dokumen yang terpilih menajdi dokumen uji.
        Tdm::whereIn('document', $list_dokumen_uji)
            ->update(['test_data' => true]);

        // Masukkan dokumen uji yang sedang diproses menjadi bagian
        // dari dokumen latih, agar terhitung pada perhitungan bobot idf.
        Tdm::where('document', $dokumen_uji)
            ->update(['test_data' => false]);

        // Lakukan perhitungan IDF
        Df::generate();

        // Lakukan seleksi Fitur
        Df::updateFeatureSelectionByIdf();

        // Term-term yang menjadi bagian dari dokumen uji yang sedang diproses
        // diambil semua. Dengan kata lain, term-term dokumen uji yang sedang
        // diproses tidak dikenakan seleksi fitur.
        $terms_dokumen_uji = Tdm::where('document', $dokumen_uji)->get();
        foreach ($terms_dokumen_uji as $term){
            echo "KNN::seleksi_fitur() -> update term dok_uji {$dokumen_uji} agar terseleksi : {$term->term} \r\n";

            Df::where('term', '=', $term->term)->update(['feature_selection' => true]);
        }

    }

    /**
     * Digunakan untuk mendapatkan nilai panjan vektor dokumen
     * yang ada pada table tf_idfs
     *
     * @param $id_dokumen
     * @return int panjang vektor
     */
    public function get_panjang_vektor($id_dokumen){
        $vektor = DB::table('tf_idfs')
            ->select(DB::raw('SUM(POW(tf_idfs.tf_idf,2)) AS panjang_vektor'))
            ->where('document', $id_dokumen)
            ->first();

        return $vektor->panjang_vektor == null ? 0 : $vektor->panjang_vektor;
    }

    /**
     * Digunakan untuk mendaptkan list_dokumen_latih.
     *
     * @param array $list_dokumen_uji
     * @return array list_dokumen_latih
     */
    public function get_list_dokumen_latih( $list_dokumen_uji = array()){
        $list_dokumen_latih = array();

        for($i = 1; $i <= 600; $i++ ){
            if ( ! in_array($i, $list_dokumen_uji)){
                $list_dokumen_latih[] = $i;
            }
        }

        return $list_dokumen_latih;
    }

}
