<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KFold extends BaseModel
{
    
	public static function useKFoldForTdm($id)
	{
		$k_fold = KFold::find($id);
		if ($k_fold != null) {
			// Mengambil string documents yang berisi kumpulan docno yang dipisahkan menggunakan koma dan di explode/split berdasarkan koma menjadi array			
			$documents = explode(",", $k_fold->documents); 

			// Update kolom test_data menjadi true semua tdm yang termasuk dalam dokumen uji 
			Tdm::whereIn('document', $documents)->update(['test_data' => true]); 
			// Update kolom test_data menjadi false semua tdm yang tidak termasuk dalam dokumen uji 
			Tdm::whereNotIn('document', $documents)->update(['test_data' => false]); 

			return true;
		}
		return false;
	}

}
