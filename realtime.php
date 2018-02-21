<?php include 'common.php';?>
<?php include 'session.php';?>
<?php include 'header.php';?>
<?php
	$result_type = isset($_GET['result_type']) ? $_GET['result_type'] : '';
	$chart_type = isset($_GET['chart_type']) ? $_GET['chart_type'] : '';
	$draw_cnt = isset($_GET['draw_cnt']) ? $_GET['draw_cnt'] : '50';
	$sampling_rate = isset($_GET['sampling_rate']) ? $_GET['sampling_rate'] : '1';

	if ($chart_type == '') {
		$chart_type = 'spline';
	}

	//set default result type
	if ($result_type == '')
		$result_type = 'resultglobal';

	if ($conn_ret == "") {
		//$remote_ipaddr = getServerSessionIpAddrList();
		//die("Device is not connected");
	} else {
		$plist = getProcessList();
		//echo $plist;
		$arr_process = preg_split('/[\s]+/', $plist);
	}	
?>
<!DOCTYPE HTML>
<html lang="ko">

<script type="text/javascript" language="javascript">
	function ResultSelect() {
		var type = document.getElementById('result_type').value;
		var chart = document.getElementById('chart_type').value;
		var sampling_rate = document.getElementById('sampling_rate').value;
		location.href = './realtime.php?result_type='+type+'&chart_type='+chart+'&sampling_rate='+sampling_rate;
	}
</script>

<script>
	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}

	function request_memtrim() {
		if (conn == '') {
			return;
		}
		$.ajax({
			url: 'live-server-data.php?result_type=memtrim',
			success: function(point) {
				alert(point);
			},
		});
	}
	function request_dropcache() {
		if (conn == '') {
			return;
		}
		$.ajax({
			url: 'drop_cache.php',
			success: function(point) {
				alert('cache dropped');
			},
		});
	}
</script>

<?php
	if ($result_type == "resulthmiapp" || $result_type == "resultsystem") {
		include 'realtime_process.php';
	} else if ($result_type == "resultglobal") {
		include 'realtime_global.php';
	} else { 
		include 'realtime_global.php';
	}		
?>

<?php include 'loginout.php';?>

<form name="form" method="post">
<?php
	//without "COMMAND"
	if ($conn_ret != "") {
?>
<select id="result_type" onchange="ResultSelect();">
<option value="resultglobal">Global</option>
<option value="resulthmiapp">HMIAPP</option>
<option value="resultsystem">Process</option>
<option value="">----------------</option>
<?php
		for($i=1;$i< sizeof($arr_process);$i++){
			$arr_name = explode("/", $arr_process[$i]);
			$j = count($arr_name) - 1;
?>
<option value="<?php echo $arr_name[$j]?>"><?php echo $arr_name[$j]?></option>
<?php
		} 
?>
</select>

<select id="chart_type" onchange="changeType();">
<option value="line">Line</option>
<option value="spline">Spline</option>
<option value="column">Stacked Column</option>
<option value="bar">Bar</option>
<option value="area">Area</option>
</select>

<select id="sampling_rate" onchange="ResultSelect('result');">
<option value="1">1 sec</option>
<option value="2">2 sec</option>
<option value="3">3 sec</option>
<option value="5">5 sec</option>
<option value="10">10 sec</option>
<option value="15">15 sec</option>
<option value="20">20 sec</option>
<option value="25">25 sec</option>
<option value="30">30 sec</option>
</select>
<script language="javascript">
	document.form.result_type.value= '<?php echo $result_type?>';
	document.form.chart_type.value= '<?php echo $chart_type?>';
	document.form.sampling_rate.value= '<?php echo $sampling_rate?>';
</script>
</form>
<script language="javascript">
	document.form.result_type.value= '<?php echo $result_type?>';
</script>
<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<div id="container" style="min-height: 400px; margin: 0 auto"></div>
<div id="cpucontainer" style="min-height: 400px; margin: 0 auto"></div>
<div id="vmcontainer" style="min-height: 400px; margin: 0 auto"></div>
<?php
	}
?>
<?php include 'footer.php';?>
</body>
</html>
