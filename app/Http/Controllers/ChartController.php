<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChartController extends Controller
{
	
    public function index($session_id)
    {
    	$data = \App\Session\SessionFile::get($session_id);
    	\App\Session\SessionFile::forget($session_id);
    	return view('chart.index', compact('data'));
    }

}
