<?php

namespace App\Http\Controllers;

use App\Tdm;
use App\TfIdf;
use App\TfIdfUji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Df;
use App\KFold;
use App\PanjangVektor;

class KnnController extends Controller {
	
    public function index(){
		return 'index';
    }
	
	public function run($K = 5){
		for($i=8; $i<=10; $i++){
			$this->doClassify($K, $i);
		}
	}
	
	public function doClassify($K  = 5, $fold = 1){
		date_default_timezone_set('Asia/Jakarta');
		$time_start = microtime(true);
	
		$file_log_name = $fold . "_knn_" . date("Ymd_His") . '.txt';
		$log_handle    = fopen($file_log_name, 'a') or die('Cannot open file:  '.$file_log_name);
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
	
	public function knnClassifier($log_handle, $K, $fold){
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
		

		
		// Ambil Data kfold
		$row_kfold = KFold::find($fold);
		
		// Ambil dokumen-dokumen yang menjadi data uji (test_data)
		$list_dokumen_uji = NULL; 
		if ( $row_kfold != NULL ){
			$list_dokumen_uji = explode(",", $row_kfold->documents); 
		} else {
			fwrite($log_handle, "Data dokumen uji belum ada. periksa tabel kfold  \r\n");
			die("Data dokumen uji belum ada. periksa tabel kfold");
		}
		sort($list_dokumen_uji);


        // MEMBUAT TF.IDF DATA LATIH
        // ----------------------------------------
		// Update data uji di table tdms
		DB::table('tdms')->update(array('test_data' => false));
		Tdm::whereIn('document', $list_dokumen_uji)->update(['test_data' => true] );

		// Create DF (IDF) Data Latih
        echo "-> Membuat idf data latih \n";
		Df::generate();
		Df::updateFeatureSelectionByIdf();

		// Create TF.IDF Data Latih
        echo "-> Membuat tf.idf data latih \n";
        TfIdf::generate();
        // --------------------------------
        // AKHIR MEMBUAT TF.IDF DATA LATIH
//        die('Selesai');

		
		// Ambil data dokumen latih, isinya array biasa
        // TODO: Bisa dioptimasi dengan looping 600 kali, yang ada di data uji di skip
		$list_dokumen_latih = $this->get_list_dokumen_latih( $list_dokumen_uji );
		
//		echo "Panjang data latih " . count($list_dokumen_latih) . "\n";
		
		// Lakukan looping terhadap semua data uji // count($list_dokumen_uji)
		for ( $i = 0; $i < count($list_dokumen_uji); $i++ ){
			$doc_id_uji = $list_dokumen_uji[$i];

            // MEMBUAT TF.IDF DATA UJI
            // Update data uji di table tdms
            echo "-> Membuat idf data uji \n";
            DB::table('tdms')->update(array('test_data' => false));
            $list_dokumen_to_update = [];

            for($x = 0; $x < count($list_dokumen_uji); $x++){
                if ( $list_dokumen_uji[$x] != $doc_id_uji ){
                    $list_dokumen_to_update[] = $list_dokumen_uji[$x];
                }
            }

            Tdm::whereIn('document', $list_dokumen_to_update )->update(['test_data' => true] );
            Df::generate();
            echo "-> Membuat tf.idf data uji \n";
            TfIdfUji::generate($doc_id_uji);

            // Ambil semua Panjang Vektor Dokumen
            $all_panjang_vektor = $this->get_all_panjang_vektor();


			// Ambil kelas dokumen uji
			if ($doc_id_uji <= 300 ){
				$KELAS_AKTUAL = "HOAX";
			} else {
				$KELAS_AKTUAL = "NONHOAX";
			}
			
            echo ($i+1) . ". Mengklasifikasi Doc " . $doc_id_uji . "\n";
			fwrite($log_handle, ($i+1) . ". Mengklasifikasi Doc " . $doc_id_uji . " [". $KELAS_AKTUAL . "] ");
			
			// Ambil semua term yang ada pada data uji
//			$term_dokumen_uji = $this->get_term_dan_tf_idf_by_doc_id($doc_id_uji);
			
			// Hitung Panjang vektor data uji
			$panjang_vektor_uji = TfIdfUji::getPanjangVektorDokUji();
			
			// Array jarak
			$jarak_knn = array();
					
			// Looping sebanyak data latih
			for( $j = 0; $j < count($list_dokumen_latih); $j++ ){
				// Log
				echo "    [".($i+1)."][" . ($j+1) .  "] Handle uji: " . $doc_id_uji . " dengan latih : " . $list_dokumen_latih[$j] . "\r\n";
				
				// Menghitung jarak cosine
				$sigma_kali           = $this->get_sigma_pembilang( $list_dokumen_latih[$j]);
				$panjang_vektor_latih = $all_panjang_vektor[$list_dokumen_latih[$j]];
				
				// Hitung jarak cosine
				$jarak = $sigma_kali / ($panjang_vektor_uji * $panjang_vektor_latih);
				
				// Jarak
				// echo "    Jarak doc " . $doc_id . " dengan doc " . $list_dokumen_latih[$j] . " : " . $jarak . "\n";
				// fwrite($log_handle, "      -> Jarak doc " . $doc_id . " dengan doc " . $list_dokumen_latih[$j] . " : " . $jarak . "\r\n");
				
				// Masukkan hasil ke array penyimpanan
				$jarak_knn[$list_dokumen_latih[$j]] = $jarak;
				
			}

			
			// Sorting nilai dari besar ke kecil
			arsort($jarak_knn);
					
			// Votting			
			$hoax     = 0;
			$non_hoax = 0;
			$banyak_vote = 0;
			
			foreach($jarak_knn as $r_doc_id => $r_jarak ){
				if($banyak_vote == $K ){
					break;
				}
				
				if ( $r_doc_id <= 300 ){
					$hoax++;
				} else {
					$non_hoax++;
				}
				$banyak_vote++;
			}
			
			if ($hoax > $non_hoax ){
				$KELAS_PREDIKSI = "HOAX";
				echo "    HASILNYA  doc_id " . $doc_id_uji . " adalah HOAX \n";
				fwrite($log_handle, " hasil : HOAX \r\n");
			} else {
				$KELAS_PREDIKSI = "NONHOAX";
				echo "    HASILNYA  doc_id " . $doc_id_uji . " adalah NONHOAX \n";
				fwrite($log_handle, " hasil : NONHOAX \r\n");
			}
			
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


		} // End Looping data uji

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

	public function get_list_dokumen_latih($dokumen_uji = array()){
		$documents = DB::table('tdms')
						->select('document')
						->distinct()
						->whereNotIn('document', $dokumen_uji)
						->orderByRaw('document')
						->get();
						
		$dokumen_latih = array();
		
		foreach($documents as $doc){
			$dokumen_latih[] = $doc->document;			
		}
		
		sort($dokumen_latih);
		
		return $dokumen_latih;
	}

	public function get_term_dan_tf_idf_by_doc_id($doc_id){
		$terms = DB::table('tf_idfs')
						->select('term', 'tf_idf', 'document')			
						->where('document', '=', $doc_id)						
						->get();
		
		$term_data_uji = array();
		
		foreach($terms as $term){
			$term_data_uji[$term->term] = $term->tf_idf;			
		}		
		
		return $term_data_uji;
	}
	
	public function get_sigma_pembilang( $doc_id_dokumen_latih ){
		$sigma_kali = 0;
		
		$array_term_dok_latih = $this->get_term_dan_tf_idf_by_doc_id($doc_id_dokumen_latih);
		
//		foreach( $dokumen_uji as $term => $tf_idf_data_uji ){
//			if ( array_key_exists($term, $array_term_dok_latih) ) {
//				$tf_idf_data_latih  = $array_term_dok_latih[$term];
//				$sigma_kali        += $tf_idf_data_uji * $tf_idf_data_latih;
//			}
//		}

        $terms_dok_uji = TfIdfUji::get();
        foreach($terms_dok_uji as $term ){
            if ( array_key_exists($term->term, $array_term_dok_latih) ) {
                $tf_idf_data_latih  = $array_term_dok_latih[$term->term];
                $sigma_kali        += $term->tf_idf * $tf_idf_data_latih;
            }
        }

		
		return $sigma_kali;
	}
		
	public function get_all_panjang_vektor(){
        // Ambil panjang vektor
        PanjangVektor::generate();

		$pv    = new \App\PanjangVektor;
		$r_pv  = $pv->get();
		$panjang_vektor = array();
		foreach($r_pv as $tf_idf ){
			$panjang_vektor[$tf_idf->doc_id] = $tf_idf->panjang_vektor;
		}
		
		return $panjang_vektor;
	}
}

