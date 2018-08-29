<?php
/**
 * Created by   : Ahmad Isyfa'lana Amin
 * Date created : 03-12-2017
 * Email        : isyfalana@gmail.com
 */

namespace App\Http\Controllers;

use App\Tdm;
use App\Df;
use App\KFold;
use Illuminate\Support\Facades\DB;

// MultiNomial Naive Bayes
class MNNB extends Controller
{

    public function run(){
        /// Ada perubahan  di branch revisi
        for($i=1; $i<= 10; $i++){
            $this->doClassify($i);
        }
    }

    public function doClassify($fold = 1){
        echo ":: DATA KFOLD : " . $fold . "\r\n";

        date_default_timezone_set('Asia/Jakarta');
        $time_start = microtime(true);

        $file_log_name = $fold . "_multinomial_nb_" . date("Ymd_His") . '.txt';
        $log_handle    = fopen($file_log_name, 'a') or die('Cannot open file:  '.$file_log_name);
        fwrite($log_handle, "Log Penelitian Multinomial NB Classification \r\n");
        fwrite($log_handle, "---------------------------- \r\n");
        fwrite($log_handle, "Waktu penelitian : ". date("Y-m-d H:i:s") . "\r\n");
        fwrite($log_handle, "Data KFold Ke    : ". $fold . "\r\n");
        fwrite($log_handle, "-----------------------------------------------------------  \r\n");


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

        // Update data
        KFold::useKFoldForTdm( $fold );

        // Pembobotan & Seleksi Fitur
        Df::generate();
        Df::updateFeatureSelectionByIdf();

        // Ambil statistik dokumen
        $jumlah_dokumen_hoax     = Tdm::getTotalDocument(0,'HOAX');
        $jumlah_dokumen_non_hoax = Tdm::getTotalDocument(0,'NONHOAX');
        $total_dokumen           = Tdm::getTotalDocument();

        // Hitung PRIOR
        $peluang_prior_hoax     = log($jumlah_dokumen_hoax     / $total_dokumen, 10);
        $peluang_prior_non_hoax = log($jumlah_dokumen_non_hoax / $total_dokumen, 10);

        // Hitung CONDITIONAL PROBABLILITY
        $rs_dokumen_uji   = KFold::find( $fold );
        $list_dokumen_uji = explode(",", $rs_dokumen_uji->documents);
        sort($list_dokumen_uji);

        $counter_dok_uji = 1;

        // Melakukan klasifikasi ke semua data uji
        foreach($list_dokumen_uji as $dokumen_uji ){

            echo "::{$counter_dok_uji} Mengklasifikasikan fold : " . $fold . " dokumen: " . $dokumen_uji . "\r\n";
            fwrite($log_handle, $counter_dok_uji . ". Mengklasifikasikan fold : " . $fold . " dokumen: " . $dokumen_uji . "\r\n");
            $counter_dok_uji++;

            $KELAS_AKTUAL = $this->get_kelas_aktual($dokumen_uji);

            // Peluang
            $peluang_hoax     = $peluang_prior_hoax;
            $peluang_non_hoax = $peluang_prior_non_hoax;

            fwrite($log_handle, "   Data Prior \r\n");
            fwrite($log_handle, "   ---------- \r\n");
            fwrite($log_handle, "   P(Hoax)     = " .  $peluang_prior_hoax . "\r\n");
            fwrite($log_handle, "   P(Non Hoax) = " .  $peluang_prior_non_hoax . "\r\n");
            fwrite($log_handle, "   ---------- \r\n");

            $jumlah_term_dokumen_hoax        = $this->get_jumlah_term_dokumen_hoax();
            $jumlah_term_dokumen_non_hoax    = $this->get_jumlah_term_dokumen_non_hoax();
            $jumlah_term_unik_dokumen_latih  = $this->get_jumlah_term_unik_dokumen_latih();

            fwrite($log_handle, "   Jumlah term di hoax    : {$jumlah_term_dokumen_hoax} \r\n");
            fwrite($log_handle, "   Jumlah term di nonhoax : {$jumlah_term_dokumen_non_hoax} \r\n");
            fwrite($log_handle, "   Jumlah term unik       : {$jumlah_term_unik_dokumen_latih} \r\n");


            // Ambil semua term dari data uji
            $tdms = Tdm::where('document', $dokumen_uji )->where('frequency','<>',0)->get();

            foreach ($tdms as $tdm) {

                // Jika term tidak terseleksi fitur, maka tidak dilakukan perhitungan
                if ( ! $this->is_selected($tdm->term)){
                    continue;
                }

                $frekuensi_term_di_hoax     = $this->get_frekuensi_kata_dalam_kelas( $tdm->term, "HOAX");
                $frekuensi_term_di_non_hoax = $this->get_frekuensi_kata_dalam_kelas( $tdm->term, "NONHOAX");

                $peluang_hoax     += log(( $frekuensi_term_di_hoax     + 1 ) / ( $jumlah_term_dokumen_hoax     +  $jumlah_term_unik_dokumen_latih ), 10) * $tdm->frequency;
                $peluang_non_hoax += log(( $frekuensi_term_di_non_hoax + 1 ) / ( $jumlah_term_dokumen_non_hoax +  $jumlah_term_unik_dokumen_latih ), 10) * $tdm->frequency;

                fwrite($log_handle, "   - P({$tdm->term}|hoax ) = "    .  ( $frekuensi_term_di_hoax     + 1 ) / ( $jumlah_term_dokumen_hoax +  $jumlah_term_unik_dokumen_latih ) . "\t");
                fwrite($log_handle, "   - P({$tdm->term}|nonhoax ) = " .  ( $frekuensi_term_di_non_hoax + 1 ) / ( $jumlah_term_dokumen_hoax +  $jumlah_term_unik_dokumen_latih ) . "\r\n");

            }

            // Menentukan Kelas
            if ( $peluang_hoax > $peluang_non_hoax ){
                $KELAS_PREDIKSI = "HOAX";
            } else {
                $KELAS_PREDIKSI = "NONHOAX";
            }

            echo "   Hasil P(Hoax) = {$peluang_hoax} P(NONHOAX) = {$peluang_non_hoax}  => " . $KELAS_PREDIKSI . "\r\n";
            fwrite($log_handle, "   ------------------------------------------------------------------------------------\r\n");
            fwrite($log_handle, "   :: ID Dokumen        : " . $dokumen_uji . "\r\n");
            fwrite($log_handle, "   :: Peluang Hoax      : " . $peluang_hoax . "\r\n");
            fwrite($log_handle, "   :: Peluang NonHoax   : " . $peluang_non_hoax . "\r\n");
            fwrite($log_handle, "   :: Hasil Klasifikasi : " . $KELAS_PREDIKSI . "\r\n");
            fwrite($log_handle, "   :: Kelas Aktual      : " . $KELAS_AKTUAL . "\r\n\r\n");


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

        echo "   ::Akurasi   = " .  ( ($TP + $TN) / ( $TP + $FP + $FN + $TN) ) . "\r\n";
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

        // Catat waktu selesai eksekusi
        $time_end       = microtime(true);
        $execution_time = ($time_end - $time_start)/60;
        echo "Waktu eksekusi program: ". $execution_time ." menit\n";
        fwrite($log_handle, "Jam Selesai            : ". date("Y-m-d H:i:s") ."\r\n");
        fwrite($log_handle, "Waktu eksekusi program : ". round($execution_time, 2) ." menit \r\n");

        fclose($log_handle);
        return 0;

    }

    public function get_kelas_aktual($id_dokumen){
        return $id_dokumen <= 300 ? "HOAX" : "NONHOAX";
    }

    public function is_selected($term){
        $sql = "SELECT dfs.term FROM dfs WHERE term = ? AND feature_selection = 1";
        $rs  = DB::select($sql, [$term]);
        if ( $rs != NULL ){
            return true;
        } else {
            return false;
        }
    }

    public function get_frekuensi_kata_dalam_kelas($term, $kelas){
        $sql = "SELECT SUM(frequency) AS jumlah_term
                FROM tdms, dfs
                WHERE tdms.class = '". $kelas . "' AND
                    tdms.term = dfs.term AND
                    dfs.feature_selection = 1 AND
                    tdms.test_data = 0 AND
                    tdms.term = '" . $term ."';";
        $rs  = DB::select($sql);
        if ( $rs != NULL ){
            return $rs[0]->jumlah_term == NULL ? 0 : $rs[0]->jumlah_term ;
        } else {
            return 0;
        }
    }

    public function get_jumlah_term_dokumen_hoax(){
        $sql = "SELECT SUM(frequency) AS jumlah_term
                FROM tdms, dfs
                WHERE tdms.term = dfs.term AND
                  dfs.feature_selection = 1 AND
                  tdms.class = 'HOAX' AND
                  tdms.test_data = 0;";
        $rs  = DB::select($sql);
        if ( $rs != NULL ){
            return $rs[0]->jumlah_term == NULL ? 0 : $rs[0]->jumlah_term ;
        } else {
            return 0;
        }
    }

    public function get_jumlah_term_dokumen_non_hoax(){
        $sql = "SELECT SUM(frequency) AS jumlah_term
                FROM tdms, dfs
                WHERE tdms.term = dfs.term AND
                  dfs.feature_selection = 1 AND
                  tdms.class = 'NONHOAX' AND
                  tdms.test_data = 0;";
        $rs  = DB::select($sql);
        if ( $rs != NULL ){
            return $rs[0]->jumlah_term == NULL ? 0 : $rs[0]->jumlah_term ;
        } else {
            return 0;
        }
    }

    public function get_jumlah_term_unik_dokumen_latih(){
        $sql = "SELECT COUNT(*) as jumlah_term FROM dfs WHERE feature_selection = 1";
        $rs  = DB::select($sql);
        if ( $rs != NULL ){
            return $rs[0]->jumlah_term == NULL ? 0 : $rs[0]->jumlah_term ;
        } else {
            return 0;
        }
    }


}
