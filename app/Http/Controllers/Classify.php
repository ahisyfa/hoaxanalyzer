<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Tdm;
use App\Df;
use App\KFold;
use Illuminate\Support\Facades\DB;

class Classify extends Controller {

    public function index(){
        $data = [];
        return view('index.index', compact('data'));
    }

    public function submit(){
        $berita  = $_POST['teks'];
        $metode  = $_POST['metode'];

        if ( $metode == 1 ){
            // Metode KNN
            $this->knn_classifier(5, $berita);
        } else if ( $metode == 2 ){
            // Metode Multinomial Naive Bayes
            $this->mnb_classifier($berita);
        }

    }

    public function knn_classifier($k = 5, $teks){
        // Praproses
        $kalimat = $this->normalization($teks);
        $kalimat = $this->removePunctuation($kalimat);
        $kalimat = strtolower($kalimat);
        $kalimat = $this->removeStopwords($kalimat);
        $terms   = $this->tokenization_unigram($kalimat);

        // Pembobotan (Bisa dilakukan hanya seklai, saat aplikasi akan running)
        // TfIdf::generate();


        $this->helper_knn_classifier($k,$terms);

    }

    public function mnb_classifier($teks){
        // Praproses
        $kalimat = $this->normalization($teks);
        $kalimat = $this->removePunctuation($kalimat);
        $kalimat = strtolower($kalimat);
        $kalimat = $this->removeStopwords($kalimat);
        $terms   = $this->tokenization_unigram($kalimat);

        // Membuat seluruh data yang ada di kopus menjadi data latih
        // Tdm::updated(['test_data' => false]);

        // Pembobotan & Seleksi Fitur
        // Df::generate();
        // Df::updateFeatureSelectionByIdf();


        $this->helper_mnb_classifier($terms);

    }

    public function removePunctuation($string){
        $kalimat = trim(preg_replace('/\s+/', ' ', $string));
        $kalimat = preg_replace("/[^a-zA-Z ]+/", "", $kalimat);
        return $kalimat;
    }

    public function normalization($string){
        $pattern     = config('pattern');
        $replacement = config('replacement');

        $kalimat = preg_replace($pattern, $replacement, $string);
        return $kalimat;
    }

    public function removeStopwords($string){
        $stopwords 	= config('stopwords');

        $kalimat = preg_replace($stopwords, "", $string);
        return $kalimat;
    }

    public function tokenization_unigram($string){
        return explode(" ", $string);
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



    public function helper_mnb_classifier($terms = array()){
        echo "<h1>Klasifikasi Multinomial Naive Bayes</h1>";

        // Ambil statistik dokumen
        $jumlah_dokumen_hoax     = Tdm::getTotalDocument(0,'HOAX');
        $jumlah_dokumen_non_hoax = Tdm::getTotalDocument(0,'NONHOAX');
        $total_dokumen           = Tdm::getTotalDocument();

        echo "<h3>Korpus</h3>";
        echo "<ul>";
        echo "<li> Jumlah Dokuemen Hoax     : " . $jumlah_dokumen_hoax . "</li>";
        echo "<li> Jumlah Dokuemen Non Hoax : " . $jumlah_dokumen_non_hoax . "</li>";
        echo "<li> Jumlah Dokuemen : " . $total_dokumen . "</li>";
        echo "</ul>";

        // Hitung PRIOR
        $peluang_prior_hoax     = log($jumlah_dokumen_hoax     / $total_dokumen, 10);
        $peluang_prior_non_hoax = log($jumlah_dokumen_non_hoax / $total_dokumen, 10);

        echo "<h3>Peluang Prior</h3>";
        echo "<ul>";
        echo "<li> Peluang Prior Hoax     : " . $peluang_prior_hoax . "</li>";
        echo "<li> Peluang Prior  Non Hoax : " . $peluang_prior_non_hoax . "</li>";
        echo "</ul>";

        // Peluang
        $peluang_hoax     = $peluang_prior_hoax;
        $peluang_non_hoax = $peluang_prior_non_hoax;


        $jumlah_term_dokumen_hoax        = $this->get_jumlah_term_dokumen_hoax();
        $jumlah_term_dokumen_non_hoax    = $this->get_jumlah_term_dokumen_non_hoax();
        $jumlah_term_unik_dokumen_latih  = $this->get_jumlah_term_unik_dokumen_latih();

        echo "<h3>Term : </h3>";
        echo "<ol>";
        foreach ($terms as $term) {
            // Jika term tidak terseleksi fitur, maka tidak dilakukan perhitungan
            if ( ! $this->is_selected( $term ) ){
                continue;
            }

            echo "<li> {$term} </li>";

            $frekuensi_term_di_hoax     = $this->get_frekuensi_kata_dalam_kelas( $term, "HOAX");
            $frekuensi_term_di_non_hoax = $this->get_frekuensi_kata_dalam_kelas( $term, "NONHOAX");

            $peluang_hoax     += log(( $frekuensi_term_di_hoax     + 1 ) / ( $jumlah_term_dokumen_hoax     +  $jumlah_term_unik_dokumen_latih ), 10) ;
            $peluang_non_hoax += log(( $frekuensi_term_di_non_hoax + 1 ) / ( $jumlah_term_dokumen_non_hoax +  $jumlah_term_unik_dokumen_latih ), 10) ;

        }
        echo "</ol>";

        echo "Peluang Hoax     : " . $peluang_hoax . "<br/>";
        echo "Peluang Non Hoax : " . $peluang_non_hoax . "<br/>";

        if ( $peluang_hoax > $peluang_non_hoax ){
            echo "<h2>Berita tersebut adalah HOAX</h2>";
        } else {
            echo "<h2>Berita tersebut BUKAN HOAX</h2>";
        }

    }

    public function helper_knn_classifier($K, $terms = array()){
        echo "<h1>Klasifikasi K-Nearest Neighbour</h1>";

        // Ambil statistik dokumen
        $jumlah_dokumen_hoax     = Tdm::getTotalDocument(0,'HOAX');
        $jumlah_dokumen_non_hoax = Tdm::getTotalDocument(0,'NONHOAX');
        $total_dokumen           = Tdm::getTotalDocument();

        echo "<h3>Korpus</h3>";
        echo "<ul>";
        echo "<li> Jumlah Dokuemen Hoax     : " . $jumlah_dokumen_hoax . "</li>";
        echo "<li> Jumlah Dokuemen Non Hoax : " . $jumlah_dokumen_non_hoax . "</li>";
        echo "<li> Jumlah Dokuemen : " . $total_dokumen . "</li>";
        echo "</ul>";

        echo "<h3>Term : </h3>";
        echo "<ol>";
//        foreach ($terms as $term) {
//            // Jika term tidak terseleksi fitur, maka tidak dilakukan perhitungan
//            if ( ! $this->is_selected( $term ) ){
//                continue;
//            }
//
//            echo "<li> {$term} </li>";
//
//        }
        echo "</ol>";

        $TF_IDF = array();

        $dfs = Df::where('feature_selection', true)->get();
        foreach ($dfs as $df) {

            $tdms = Tdm::where('test_data', false)->where('term', $df->term)->get();

            foreach ($tdms as $tdm) {

                $TF_IDF[$tdm->term] = array(
                    'freq'     => $tdm->frequency,
                    'df'       => $df->df,
                );

            }

        }

        foreach ($terms as $term) {
            if ( array_key_exists($term, $TF_IDF) ){
                $TF_IDF[$term]['freq']++;
                $TF_IDF[$term]['df']++;
            } else {
                $TF_IDF[$term] = array(
                    'freq'     => 1,
                    'df'       => 1,
                );
            }
        }



        print_r($TF_IDF);




    }

}
