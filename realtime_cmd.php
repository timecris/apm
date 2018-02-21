<?php include 'common.php';?>
<?php include 'session.php';?>
<?php include 'header.php';?>
<?php
	$example = "cat /proc/meminfo | awk '/MemFree/ {mf=$2} /Dirty/ {dt=$2} /Active:/ {ac=$2} /Inactive:/ {iac=$2} END {printf \"4, MEMFREE, DIRTY, ACTIVE, INACTIVE, %d, %d, %d, %d\", mf, dt, ac, iac}'";

	$cmd = isset($_POST['cmd']) ? $_POST['cmd'] : $example;
	$cmd = addslashes($cmd);
?>
<!DOCTYPE HTML>
<html lang="ko">
<script type="text/javascript" language="javascript">
	function ResultSelect() {
		var id = document.getElementById('result_id').value;
		var type = document.getElementById('result_type').value;
		location.href = './index.php?result_id='+id+'&result_type='+type;
	}
</script>

<script>
	var chart; // global
	var init = 0;
	cmd = encodeURIComponent('<?php echo $cmd ?>');

	function requestData() {
		$.ajax({
			//type: "POST",			
			url: "live_cmd.php?cmd="+cmd, 
			//url: "live-server-data.php",
			success: function(point) {				
				//alert(point);
				var strArray = String(point).split(',');
				//type casting
				var time = strArray[0] * 1;		//time
				//alert(time);
				var count = strArray[1] * 1;	//count of item
				//alert(count);
				var base = 2;					//base index of array

				if (init == 0) {
					for (var i=0; i < count; i++) {
						chart.addSeries({
							name: strArray[base+i], 
							data: [],
							visible: false,
						}, false);
					}
					//show only the AFM				
					chart.series[0].visible = true;
					var shift = chart.series[0].data.length > 20; // shift if the series is longer than 20

					init = 1;
				}
				
				//base += count + 1;
				base += count;
				
				//addPoint
				for (var i=0; i < count; i++) {
					//type casting
					strArray[base+i] *= 1;
					//alert(strArray[base+i]);
					chart.series[i].addPoint([time, strArray[base+i]], true, shift);
				}

				// call it again after one second
				setTimeout(requestData, 1000);	
			},
			cache: false
		});
	}

	$(document).ready(function() {
		chart = new Highcharts.Chart({
			chart: {
				renderTo: 'container',
				defaultSeriesType: 'spline',
				events: {
					load: requestData
				}
			},
			title: {
				text: ''
			},
			xAxis: {
				type: 'datetime',
				tickPixelInterval: 300,
				maxZoom: 20 * 1000
			},
			yAxis: {
				minPadding: 0.2,
				maxPadding: 0.2,
				title: {
					text: '',
					margin: 80
				}
			},
			series: []
		});
	});
</script>

<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<body>
<br>
<?php include 'loginout.php';?>

<form name="form" action="realtime_cmd.php" method="post">
<?php
	//without "COMMAND"
	if ($conn_ret != "") {
?>
<input name="cmd" value="" size="200">
<input type="submit" value="ENTER" />
<br>
<?php 
	}
?>
</form>
<div id="container" style="min-width: 310px; height: 650px; margin: 0 auto"></div>
<?php include 'footer.php';?>
</body>
<script>
	document.form.cmd.value = decodeURIComponent(cmd);
</script>
</html>
