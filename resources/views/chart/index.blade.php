@extends('chart.layout')
@section('content')

<div id="container" style="min-width: 310px; height: 400px; max-width: 800px; margin: 0 auto"></div>

<script type="text/javascript">
	var settings = <?= json_encode($data) ?>;
	settings.tooltip.formatter = function () {
		return 'x : '+ this.point.x +'<br/>y : '+ this.point.y +'<br/>label : '+ this.point.label;
	}
	Highcharts.chart('container', settings);
</script>
	

@endsection