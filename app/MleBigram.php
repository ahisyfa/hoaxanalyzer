<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MleBigram extends BaseModel
{

    public static function generate()
	{
		MleBigram::truncate();
		
		$mlebigrams = DB::select('SELECT term, sum(frequency) AS freq FROM tdm_bigrams WHERE frequency <> 0 AND test_data = 0 GROUP BY term');

		foreach ($mlebigrams as $mlebigram) 
		{
			$object_mlebigram = new MleBigram();
			$object_mlebigram->term = $mlebigram->term;
			$object_mlebigram->freq = $mlebigram->freq;

			$piece = explode(" ",$mlebigram->term);
			$mle_uni = Mle::where('term', $piece[0])->first();
			$mle = $mlebigram->freq/$mle_uni->freq;

			$object_mlebigram->mle = $mle;
			$object_mlebigram->save();
		}

		return true;
	}
}
