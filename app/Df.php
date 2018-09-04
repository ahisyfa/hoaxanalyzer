<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Df extends BaseModel
{
	public static function generate()
	{
        echo "Df::generate() \r\n";

		Df::truncate();

		$dfs = DB::select('SELECT term, COUNT(term) AS df FROM tdms WHERE frequency <> 0 AND test_data = 0 GROUP BY term');
		$N   = Tdm::getTotalDocument();

		foreach ($dfs as $df) 
		{
            $object_df = new Df();
            $object_df->term = $df->term;
            $object_df->df = $df->df;
            $object_df->idf = log($N / $df->df, 10);
            $object_df->save();
            // echo "Df::generate() -> N = {$N}, Memproses term : {$df->term} \r\n";
		}

		return true;
	}

	public static function updateFeatureSelectionByIdf($min = 0.13033377, $max = 2.03342376)
	{
        echo "Df::updateFeatureSelectionByIdf() -> min = {$min}, max = {$max} \r\n";

		$dfs_1 = Df::where('idf', '>=', $min)->where('idf', '<=', $max)->update(['feature_selection' => true]);
		$dfs_0 = Df::where('idf', '<', $min)->orWhere('idf', '>', $max)->update(['feature_selection' => false]);
		return true;
	}

}
