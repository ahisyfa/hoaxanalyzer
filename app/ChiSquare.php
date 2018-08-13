<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class ChiSquare extends BaseModel
{

	public static function generate()
	{

		$contingencies = self::getContingency();
		$n_hoax = Tdm::getTotalDocument(0, 'HOAX');
		$n_nonhoax = Tdm::getTotalDocument(0, 'NONHOAX');

		if (count($contingencies)) {
			foreach ($contingencies as $key => $contingency) {
				
				$chi_square_t_hoax = new ChiSquare();
				$chi_square_t_hoax->term = $contingency->term;
				$chi_square_t_hoax->class = 'HOAX';

				$chi_square_t_nonhoax = new ChiSquare();
				$chi_square_t_nonhoax->term = $contingency->term;
				$chi_square_t_nonhoax->class = 'NONHOAX';

				$n_t_hoax = $contingency->hoax;
				$n_t_nonhoax = $contingency->nonhoax;
				$n_nt_hoax = $n_hoax - $contingency->hoax;
				$n_nt_nonhoax = $n_nonhoax - $contingency->nonhoax;

				$a = $n_t_hoax + $n_t_nonhoax + $n_nt_hoax + $n_nt_nonhoax;
				
				$b = ($n_t_hoax * $n_nt_nonhoax) - ($n_nt_hoax * $n_t_nonhoax);
				$b *= $b;
				$c_1 = $n_t_hoax + $n_nt_hoax;
				$c_2 = $n_t_hoax + $n_t_nonhoax;
				$d_1 = $n_nt_nonhoax + $n_nt_hoax;
				$d_2 = $n_nt_nonhoax + $n_t_nonhoax;

				$chi_square_t_hoax->value = ($a * $b) / ($c_1 * $c_2 * $d_1 * $d_2);
				$chi_square_t_nonhoax->value = $chi_square_t_hoax->value;

				$chi_square_t_hoax->save();
				$chi_square_t_nonhoax->save();

				$x2_t_hoax = 0;
				$x2_t_nonhoax = 0;
			}
		}

		return true;

	}

	public static function getContingency()
	{
		$query = "
				select 
					dfs.term, IF(hoax.df IS NULL, 0, hoax.df) as hoax, IF(nonhoax.df IS NULL, 0, nonhoax.df) as nonhoax
				from 
					dfs
					left join (SELECT term, COUNT(term) AS df FROM tdms WHERE class = 'HOAX' and frequency <> 0 AND test_data = 0 GROUP BY term) as hoax on hoax.term = dfs.term
					left join (SELECT term, COUNT(term) AS df FROM tdms WHERE class = 'NONHOAX' and frequency <> 0 AND test_data = 0 GROUP BY term) as nonhoax on nonhoax.term = dfs.term";

		return DB::select($query);			
	}

}
