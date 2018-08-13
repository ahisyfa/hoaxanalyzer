<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class TdmTrigram extends BaseModel
{
    public static function getTotalDocument($test_data = 0)
	{
		$N = DB::select('select COUNT(*) as N FROM (select DISTINCT document FROM tdm_trigrams WHERE test_data = ?) AS new', [$test_data]);

		return $N[0]->N;
	}
}
