<?php

namespace App\Http\Controllers;

use App\PanjangVektor;
use App\TfIdf;
use Illuminate\Http\Request;
use App\Tdm;
use App\Df;
use Illuminate\Support\Facades\DB;

class Classify extends Controller {

    public function index(){
        $data = [];
        return view('index.index', compact('data'));
    }

    public function submit(Request $request){
        $berita  = $request->teks;
        $metode  = $request->metode;

        if ( $metode == 1 ){
            // Metode KNN
            return $this->knn_classifier(5, $berita);
        } else if ( $metode == 2 ){
            // Metode Multinomial Naive Bayes
            return $this->mnb_classifier($berita);
        }

    }

    public function coba(){
        // $app = new App\Http\Controllers\Classify(); $app->coba();
        $kalimat = "Hari aku syantik, cantik bagai bidadari";

        $this->knn_classifier(5, $kalimat);
    }

    private function knn_classifier($K = 5, $teks){
        // Praproses
        $kalimat = $this->normalization($teks);
        $kalimat = $this->removePunctuation($kalimat);
        $kalimat = strtolower($kalimat);
        $kalimat = $this->removeStopwords($kalimat);
        $terms   = $this->tokenization_unigram($kalimat);

        // Pembobotan tf.idf dokumen uji
        $tf_idf_uji = $this->generate_tf_idf_dokumen_uji($terms);

        // Mmebuat Sigma Pembilang
        $sigma_pembilangs = $this->generate_sigma_pembilang($tf_idf_uji);

        // Ambil panjang vektor dokumen uji.
        $panjang_vektor_uji = $this->get_panjang_vektor_dok_uji($tf_idf_uji);

        // Jarak KNN
        $jarak_knn = array();

        // Ambil jumlah dokumen latih
        $N = Tdm::getTotalDocument();

        for($i = 1; $i <= $N; $i++){
            $sigma_pembilang = $sigma_pembilangs[$i];
            $pv = PanjangVektor::find($i);

            $jarak = $sigma_pembilang / ($panjang_vektor_uji * $pv->panjang_vektor);

            $jarak_knn[$i] = $jarak;

        }

        // Sorting nilai dari besar ke kecil
        arsort($jarak_knn);

        // Votting
        $hoax     = 0;
        $non_hoax = 0;
        $banyak_vote = 0;

        // Bobot
        $bobot_hoax     = 0;
        $bobot_non_hoax = 0;


        foreach($jarak_knn as $r_doc_id => $r_jarak ){
            if($banyak_vote == $K ){
                break;
            }

            if ( $r_doc_id <= 300 ){
                $hoax++;
                $bobot_hoax += $r_jarak;
            } else {
                $non_hoax++;
                $bobot_non_hoax += $r_jarak;
            }

            $banyak_vote++;
        }

        $KELAS_PREDIKSI = "";

        if ($hoax > $non_hoax ){
            $KELAS_PREDIKSI = "HOAX";
        } else {
            $KELAS_PREDIKSI = "NONHOAX";
        }

        $persen_hoax     = ($hoax     / $K) * 100;
        $persen_non_hoax = ($non_hoax / $K) * 100;


        $data = [
            'KELAS_PREDIKSI'  => $KELAS_PREDIKSI,
            'N'               => $N,
            'bobot_hoax'      => $bobot_hoax,
            'bobot_non_hoax'  => $bobot_non_hoax,
            'vote_hoax'       => $hoax,
            'vote_non_hoax'   => $non_hoax,
            'persen_hoax'     => $persen_hoax,
            'persen_non_hoax' => $persen_non_hoax,
        ];

        return view('index.result_knn', $data);

    }

    private function generate_tf_idf_dokumen_uji( & $terms ){
        $tf_idf_uji = array();
        $N = Tdm::getTotalDocument();

        foreach ($terms as $term){
            if ( ! array_key_exists($term, $tf_idf_uji)){
                $tf_idf_uji[$term] = [
                    'tf'    => 1,
                    'df'    => 0,
                    'idf'   => 0,
                    'tfidf' => 0,
                ];
            } else {
                $tf_idf_uji[$term]['tf']++;
            }
        }

        foreach ($tf_idf_uji as $term => $value ){
            $df = DB::table('tf_idfs')
                ->select(DB::raw('COUNT(*) AS df '))
                ->where('term', $term)
                ->first();

            $tf_idf_uji[$term]['df']    = $df->df + 1;
            $tf_idf_uji[$term]['idf']   = log($N / ($df->df + 1), 10);
            $tf_idf_uji[$term]['tfidf'] = $tf_idf_uji[$term]['tf'] * $tf_idf_uji[$term]['idf'];

        }

        return $tf_idf_uji;

    }

    private function generate_sigma_pembilang( & $tf_idfs ){
        $matrik_kali_tfidf = array();
        $sigma_pembilang   = array();

        foreach ($tf_idfs as $term => $value ){
            // Ambil tfidf dari term ini
            $terms_tfidf = TfIdf::where('term', $term)->get();

            foreach ($terms_tfidf as $item) {
                $matrik_kali_tfidf[$term][$item->document] = $tf_idfs[$term]['tfidf'] * $item->tf_idf;
            }

        }

        for ($i = 1; $i <= 600; $i++){
            $sigma_pembilang[$i] = 0;
            foreach ($tf_idfs as $term => $value ){
                $bobot = isset($matrik_kali_tfidf[$term][$i]) ? $matrik_kali_tfidf[$term][$i] : 0;
                $sigma_pembilang[$i] += $bobot;
            }
        }

        return $sigma_pembilang;
    }

    private function get_panjang_vektor_dok_uji(& $tf_idfs ){
        $panjang_vektor = 0;

        foreach ($tf_idfs as $term => $value ){
            $panjang_vektor += pow($tf_idfs[$term]['tfidf'], 2);
        }

        return sqrt($panjang_vektor);
    }

    public function mnb_classifier($teks){
        // Praproses
        $kalimat = $this->normalization($teks);
        $kalimat = $this->removePunctuation($kalimat);
        $kalimat = strtolower($kalimat);
        $kalimat = $this->removeStopwords($kalimat);
        $terms   = $this->tokenization_unigram($kalimat);


        return $this->helper_mnb_classifier($terms);

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
        $kalimat = preg_replace('!\s+!', ' ', $kalimat);

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

        // Ambil statistik dokumen
        $jumlah_dokumen_hoax     = Tdm::getTotalDocument(0,'HOAX');
        $jumlah_dokumen_non_hoax = Tdm::getTotalDocument(0,'NONHOAX');
        $total_dokumen           = Tdm::getTotalDocument();

        // Hitung PRIOR
        $peluang_prior_hoax     = log($jumlah_dokumen_hoax     / $total_dokumen, 10);
        $peluang_prior_non_hoax = log($jumlah_dokumen_non_hoax / $total_dokumen, 10);


        // Peluang
        $peluang_hoax     = $peluang_prior_hoax;
        $peluang_non_hoax = $peluang_prior_non_hoax;


        $jumlah_term_dokumen_hoax        = $this->get_jumlah_term_dokumen_hoax();
        $jumlah_term_dokumen_non_hoax    = $this->get_jumlah_term_dokumen_non_hoax();
        $jumlah_term_unik_dokumen_latih  = $this->get_jumlah_term_unik_dokumen_latih();


        foreach ($terms as $term) {
            // Jika term tidak terseleksi fitur, maka tidak dilakukan perhitungan
            if ( ! $this->is_selected( $term ) ){
                continue;
            }

            $frekuensi_term_di_hoax     = $this->get_frekuensi_kata_dalam_kelas( $term, "HOAX");
            $frekuensi_term_di_non_hoax = $this->get_frekuensi_kata_dalam_kelas( $term, "NONHOAX");

            $peluang_hoax     += log(( $frekuensi_term_di_hoax     + 1 ) / ( $jumlah_term_dokumen_hoax     +  $jumlah_term_unik_dokumen_latih ), 10) ;
            $peluang_non_hoax += log(( $frekuensi_term_di_non_hoax + 1 ) / ( $jumlah_term_dokumen_non_hoax +  $jumlah_term_unik_dokumen_latih ), 10) ;

        }

        $KELAS_PREDIKSI = "";


        if ( $peluang_hoax > $peluang_non_hoax ){
            $KELAS_PREDIKSI = "HOAX";
        } else {
            $KELAS_PREDIKSI = "BUKAN HOAX";
        }

        $jumlah_peluang = $peluang_hoax + $peluang_non_hoax;

        $peluang_hoax_dalam_positif     = $jumlah_peluang - $peluang_hoax;
        $peluang_non_hoax_dalam_positif = $jumlah_peluang - $peluang_non_hoax;

        $persen_peluang_hoax     = ($peluang_hoax_dalam_positif     / $jumlah_peluang) * 100;
        $persen_peluang_non_hoax = ($peluang_non_hoax_dalam_positif / $jumlah_peluang) * 100;

        $data = [
            'KELAS_PREDIKSI'   => $KELAS_PREDIKSI,
            'peluang_hoax'     => $peluang_hoax,
            'peluang_non_hoax' => $peluang_non_hoax,
            'persen_peluang_hoax'     => $persen_peluang_hoax,
            'persen_peluang_non_hoax' => $persen_peluang_non_hoax,
        ];

        return view('index.result_mnnb', $data);

    }


}
