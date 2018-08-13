<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DfTrigram extends BaseModel
{
    public static function generate()
	{
		DfTrigram::truncate();

		$dfs = DB::select('SELECT term, COUNT(term) AS df FROM tdm_trigrams WHERE frequency <> 0 GROUP BY term');
		$N = TdmTrigram::getTotalDocument();

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
		$dfs_1 = DfTrigram::where('idf', '>=', $min)->where('idf', '<=', $max)->update(['feature_selection' => true]);
		$dfs_0 = DfTrigram::where('idf', '<', $min)->orWhere('idf', '>', $max)->update(['feature_selection' => false]);
		return true;
	}
}
