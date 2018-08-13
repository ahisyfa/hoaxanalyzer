<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Mle extends BaseModel
{
    public static function generate()
	{
		Mle::truncate();
		
		$mles = DB::select('SELECT term, sum(frequency) AS freq FROM tdms WHERE frequency <> 0 AND test_data = 0 GROUP BY term');
		$N = Tdm::getTotalFrequency();

		foreach ($mles as $mle) 
		{
			$object_mle = new Mle();
			$object_mle->term = $mle->term;
			$object_mle->freq = $mle->freq;
			$object_mle->mle = $mle->freq/$N;
			$object_mle->save();
		}

		return true;
	}

}
