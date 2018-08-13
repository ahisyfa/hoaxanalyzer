<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DfBigram extends BaseModel
{
    public static function generate()
	{
		DfBigram::truncate();

		$dfs = DB::select('SELECT term, COUNT(term) AS df FROM tdm_bigrams WHERE frequency <> 0 GROUP BY term');
		$N = TdmBigram::getTotalDocument();

		foreach ($dfs as $df) 
		{
			$object_df = new DfBigram();
			$object_df->term = $df->term;
			$object_df->df = $df->df;
			$object_df->idf = log($N/$df->df, 10);
			$object_df->save();
		}

		return true;
	}

	public static function updateFeatureSelectionByIdf($min = 0, $max = 9999999)
	{
		$dfs_1 = DfBigram::where('idf', '>=', $min)->where('idf', '<=', $max)->update(['feature_selection' => true]);
		$dfs_0 = DfBigram::where('idf', '<', $min)->orWhere('idf', '>', $max)->update(['feature_selection' => false]);
		return true;
	}
}
