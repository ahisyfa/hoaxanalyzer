<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Tdm extends BaseModel
{

	public static function getTotalDocument($test_data = 0, $class = null)
	{
		$where = '';
		$params_where = [];

		if ($test_data !== null) {
			$where .= " test_data = ? ";
			array_push($params_where, $test_data);
		}
		if ($class !== null) {
			if ($where != '') $where .= ' AND ';
			$where .= " class = ? ";
			array_push($params_where, $class);
		}

		if ($where != '') {
			$where = " WHERE {$where}";
		}
		$query = "select COUNT(*) as N FROM (select DISTINCT document FROM tdms {$where}) AS new";
		$N = DB::select($query, $params_where);

		return $N[0]->N;
	}

	public static function getTotalFrequency($test_data = 0, $class = null)
	{
		$where = '';
		$params_where = [];

		if ($test_data !== null) {
			$where .= " test_data = ? ";
			array_push($params_where, $test_data);
		}
		if ($class !== null) {
			if ($where != '') $where .= ' AND ';
			$where .= " class = ? ";
			array_push($params_where, $class);
		}

		if ($where != '') {
			$where = " WHERE {$where}";
		}
		$query = "select SUM(frequency) as N FROM (select * FROM tdms {$where}) AS new";
		$N = DB::select($query, $params_where);

		return $N[0]->N;
	}

	public static function getUsingQuery()
	{
		return DB::select('SELECT * FROM tdms');
	}
	
}
