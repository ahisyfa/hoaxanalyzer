<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class TfIdfUji extends BaseModel
{
    
	public static function generate($doc_id)
	{

		TfIdfUji::truncate();

		$dfs = DB::table('dfs')
				->where('tdms.document', $doc_id)
				->join('tdms', 'tdms.term', '=', 'dfs.term')
				->select('dfs.*')
				->get();


		foreach ($dfs as $df) {

			$tdms = Tdm::where('document', $doc_id)
						->where('term', $df->term)
						->get();
			
			foreach ($tdms as $tdm) {
				$tfidf           = new TfIdfUji();
				$tfidf->term     = $tdm->term;
				$tfidf->tf_idf   = $df->idf * $tdm->frequency;
				$tfidf->document = $tdm->document;
				$tfidf->class    = $tdm->class;
				$tfidf->save();
			}

		}

		return true;
	}

	public static function getPanjangVektorDokUji(){
		return DB::select('SELECT  SQRT(SUM(POW(tf_idf_ujis.tf_idf, 2))) AS jarak FROM `tf_idf_ujis`')[0]->jarak;
	}

}
