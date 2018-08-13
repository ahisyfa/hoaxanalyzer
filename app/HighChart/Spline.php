<?php

namespace App\HighChart;

class Spline extends Chart
{
    
	public function generate()
	{
		$series = [];
		$multi_series = $this->multi_series;
		foreach ($this->raw_data as $key => $row) {
			$row_multi_series = null;
			$x = $this->x;
			$x = $row->$x;
			$x_string = (string)$x;
			$y = 1;
			$row_custom_label = '';
			$custom_label = $this->custom_label;
			if (!$x) continue;
			if (isset($row->$multi_series)) {
				$row_multi_series = $row->$multi_series;
			}
			if (isset($row->$y)) {
				$y = $row->$y;
			}
			if (isset($row->$custom_label)) {
				$row_custom_label = $row->$custom_label;
			}
			if (!isset($series[$row_multi_series])){
				$series[$row_multi_series]['name'] = $row_multi_series;
				$series[$row_multi_series]['data'] = [];
			}
			$data = $series[$row_multi_series]['data'];
			if (!isset($data[$x_string])) {
				$data[$x_string] = ['x' => $x, 'y' => 0, 'label' => ''];
			}
			$data[$x_string]['y'] += $y;
			if ($row_custom_label) {
				$data[$x_string]['label'] .= $row_custom_label .' ';
			}
			$series[$row_multi_series]['data'] = $data;
		}
		$this->settings['series'] = array_values($series);
		$i = 0;
		foreach ($series as $key_series => $value_series) {
			$value_series_data = $value_series['data'];
			ksort($value_series_data);
			$this->settings['series'][$i]['data'] = array_values($value_series_data);
			$i++;
		}
		$this->settings['xAxis']['title']['text'] = $this->x;
		$this->settings['yAxis']['title']['text'] = $this->y ? $this->y : 'Frekuensi';

		return $this->settings;
	}

}
