<?php
// http://jsfiddle.net/japanick/dWDE6/314/
namespace App\HighChart;

class Chart
{

	public static $available_types = [
		'line' => "\\App\\HighChart\\Line", 
		'spline' => "\\App\\HighChart\\Spline", 
		'area' => "\\App\\HighChart\\Area", 
		'bar' => "\\App\\HighChart\\Bar", 
		'column' => "\\App\\HighChart\\Column", 
		'pie' => "\\App\\HighChart\\Pie", 
		'scatter' => "\\App\\HighChart\\Scatter", 
		'bubble'  => "\\App\\HighChart\\Bubble"
	];
	protected $session_id = null;
	protected $raw_data = null;
	protected $x = null;
	protected $y = null;
	protected $categories = null;
	protected $multi_series = null;
	protected $custom_label = null;
	protected $tooltip = null;
	protected $settings = [
		'accessibility' => [],
		'annotations' => [],
		'boost' => [],
		'chart' => ['type' => 'line'],
		'colorAxis' => [],
		'colors' => [],
		'credits' => [],
		'data' => [],
		'defs' => [],
		'drilldown' => [],
		'exporting' => [],
		'labels' => [],
		'legend' => [],
		'loading' => [],
		'navigation' => [],
		'noData' => [],
		'pane' => [],
		'plotOptions' => [],
		'responsive' => [],
		'series' => [],
		'subtitle' => ['text' => ''],
		'title' => ['text' => ''],
		'tooltip' => [],
		'xAxis' => ['title' => ['text' => '']],
		'yAxis' => ['title' => ['text' => '']],
		'zAxis' => [],
	];

	public function __construct($type)
	{
		$this->session_id = md5(microtime(true));
		if (!array_key_exists($type, Chart::$available_types)) {
			echo 'Tipe tidak ditemukan. Tipe yang tersedia adalah:';
			print_r(Chart::$available_types);
			return false;
		}
		$this->settings['accessibility'] = new \stdClass;
		$this->settings['annotations'] = new \stdClass;
		$this->settings['boost'] = new \stdClass;
		$this->settings['colorAxis'] = new \stdClass;
		$this->settings['colors'] = new \stdClass;
		$this->settings['credits'] = new \stdClass;
		$this->settings['data'] = new \stdClass;
		$this->settings['defs'] = new \stdClass;
		$this->settings['drilldown'] = new \stdClass;
		$this->settings['exporting'] = new \stdClass;
		$this->settings['labels'] = new \stdClass;
		$this->settings['legend'] = new \stdClass;
		$this->settings['loading'] = new \stdClass;
		$this->settings['navigation'] = new \stdClass;
		$this->settings['noData'] = new \stdClass;
		$this->settings['pane'] = new \stdClass;
		$this->settings['plotOptions'] = new \stdClass;
		$this->settings['responsive'] = new \stdClass;
		$this->settings['series'] = new \stdClass;
		$this->settings['tooltip'] = new \stdClass;
		$this->settings['zAxis'] = new \stdClass;
		$this->settings['chart']['type'] = $type;
	}

	public static function createInstance($type)
	{
		if (!array_key_exists($type, self::$available_types)) {
			echo 'Tipe tidak ditemukan. Tipe yang tersedia adalah:';
			print_r(self::$available_types);
			return false;
		}
		return new self::$available_types[$type]($type);
	}

	public function simple($raw_data, $x, $y, $title, $multi_series = null, $categories = null, $custom_label = null, $tooltip = null)
	{
		$this->raw_data = $raw_data;
		$this->x = $x;
		$this->y = $y;
		$this->multi_series = $multi_series;
		$this->categories = $categories;
		$this->custom_label = $custom_label;
		$this->tooltip = $tooltip;
		$this->settings['title']['text'] = $title;
	}

	public function setData($raw_data)
	{
		$this->raw_data = $raw_data;
	}

	public function setSettings($settings)
	{
		$this->settings = $settings;
	}

	public function getSettings()
	{
		return $this->settings;
	}

	public function openBrowser()
	{
		\App\Session\SessionFile::put($this->session_id, $this->settings);
		shell_exec("start chrome ". env('APP_URL', 'http://localhost:8000') ."/chart/". $this->session_id);
	}

	public function __set($name, $value)
	{
		if (isset($this->settings[$name])) {
			$this->settings[$name] = $value;
		}
	}

	public function __get($name)
	{
		if (isset($this->settings[$name])) {
			return $this->settings[$name];
		}
		return null;
	}

	public function test()
	{
		$tdms = \App\Tdm::getUsingQuery();
		\App\Session\SessionFile::put($this->session_id, $tdms);
	}

	public function generate()
	{
		echo 'Fungsi ini masih kosong. Silahkan membuat instance menggunakan fungsi App\HighChart\Chart::createInstance(\'tipe_chart\'). List tipe_chart:';
		print_r(Chart::$available_types);
		return;
	}
    
}
