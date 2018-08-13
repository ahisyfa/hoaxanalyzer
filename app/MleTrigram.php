<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MleTrigram extends BaseModel
{

    public static function generate()
	{
		MleTrigram::truncate();
		
		$mletrigrams = DB::select('SELECT term, sum(frequency) AS freq FROM tdm_trigrams WHERE frequency <> 0 AND test_data = 0 GROUP BY term');

		foreach ($mletrigrams as $mletrigram) 
		{
			$object_mletrigram = new MleTrigram();
			$object_mletrigram->term = $mletrigram->term;
			$object_mletrigram->freq = $mletrigram->freq;

			$piece = explode(" ",$mletrigram->term);
			$merge = array($piece[0],$piece[1]);
			$bigram = implode(" ",$merge);
			$mle_bi = MleBigram::where('term', $bigram)->first();
			$mle = $mletrigram->freq/$mle_bi->freq;

			$object_mletrigram->mle = $mle;
			$object_mletrigram->save();
		}

		return true;
	}
}
