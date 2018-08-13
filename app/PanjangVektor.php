<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class PanjangVektor extends BaseModel
{
	
	protected $primaryKey = 'doc_id'; 
	public $incrementing = false;
	
	public static function generate()
	{
		PanjangVektor::truncate();
		for($i = 1; $i <= 600; $i++ ){
			$pv                 = new PanjangVektor();
			$pv->doc_id         = $i;
			$pv->panjang_vektor = PanjangVektor::get_panjang_vektor_dokumen($i);
			$pv->save();
		}

		return true;
	}
	
	private static function get_panjang_vektor_dokumen($doc_id){
		$terms = DB::table('tf_idfs')
						->select('term', 'tf_idf', 'document')			
						->where('document', '=', $doc_id)						
						->get();
		
		$panjang_vektor = 0;
		
		foreach($terms as $term){			
			$panjang_vektor += pow($term->tf_idf, 2);
		}
		
		return sqrt($panjang_vektor);
	}

}
