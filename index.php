<?php include 'common.php';?>
<?php   
	$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
	$result_id = isset($_GET['result_id']) ? $_GET['result_id'] : '';
	$result_type = isset($_GET['result_type']) ? $_GET['result_type'] : '';
	$chart_type = isset($_GET['chart_type']) ? $_GET['chart_type'] : '';

	$dh  = opendir($SERVER_DATA_DIR);
	if (!$dh) {
		die("directory not found");
	}

	$user_list = getDirectoryItemList($SERVER_DATA_DIR);
	if ($user_id == '' && count($user_list) > 0) { 
		$user_id = $user_list[0];
	}

	if ($user_id) {
		$result_list = getDirectoryItemList($SERVER_DATA_DIR . DS . $user_id);
		if ($result_id == '' && count($result_list) > 0) { 
			$result_id = $result_list[0];
		}
	}

	if ($result_type == '') { 
		$result_type = 'resultmeminfo';
	}

	if ($chart_type == '') { 
		$chart_type = 'line';
	}
?>
<!DOCTYPE HTML>
<html lang="ko">
<?php include 'header.php';?>
<script type="text/javascript" language="javascript">
function ResultSelect(v) {
	var user_id = document.getElementById('user_id').value;
	if (v == 'user') {
		var result_id = '';
	} else if (v == 'result') {
		var result_id = document.getElementById('result_id').value;
	}
	var type = document.getElementById('result_type').value;
	var chart = document.getElementById('chart_type').value;
	location.href = './index.php?user_id='+user_id+'&result_id='+result_id+'&result_type='+type+'&chart_type='+chart;
}
</script>
<script type="text/javascript">
jQuery(document).ready(function() { 
	var options = {
		chart: {
			type: '<?php echo $chart_type?>',
			renderTo: 'container',
		},
		title: {
			text: ''  
		},
		xAxis: {
			categories: []
		},
		yAxis: {
			title: {
				text: 'MiB, KiB'
			}
		},
        tooltip: {
            shared: true
        },
        plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true,
                    color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                    style: {
                        textShadow: '0 0 3px black'
                    }
                }
            }
        },
		series: []
	};
	// JQuery function to process the csv data
	$.get('data/<?php echo $user_id?>/<?php echo $result_id?>/<?php echo $result_type?>', function (data) {
		// Split the lines
		var lines = data.toString().split('\n');
		$.each(lines, function(lineNo, line) {
			var items = line.split(',');
			// header line contains names of categories
			if (lineNo == 0) {
				$.each(items, function(itemNo, item) {
				//skip first item of first line
					if (itemNo > 0) options.xAxis.categories.push(item);
				});
			}
			// the rest of the lines contain data with their name in the first position
			else {
				var series = { 
					data: [],
					visible: false,
				};
				$.each(items, function(itemNo, item) {
					if (itemNo == 0) {
						series.name = item;
					} else {
						series.data.push(parseFloat(item));
					}
				});
				//show only the AFM
				if (document.form.result_type.value == 'resultmeminfo')
				{
					if (options.chart.type == 'line')
					{
						if (series.name == "AFM")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (series.name == "Buffers:" || series.name == "Cached:" || series.name == "Active:" || series.name == "Mapped:" || series.name == "Slab:")
						{
							series.visible = true;
						}
					}
				} 
				else if (document.form.result_type.value == 'resultpss')
				{
					if (options.chart.type == 'line')
					{
						if (series.name == "QtWebProcess")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (series.name == "QtWebProcess" || series.name == "LSM" || series.name == "MaliitServer" || series.name == "WebAppMgr" || series.name == "SYSTEMSERVICE")
						{
							series.visible = true;
						}
					}
				}
				else if (document.form.result_type.value == 'resultQtWebProcess')
				{
					if (options.chart.type == 'line')
					{
						if (series.name == "heap" || series.name == "anon")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (lineNo < 6)
						{
							series.visible = true;
						}
					}
				}
				else if (document.form.result_type.value == 'resultWebAppMgr') {
					if (options.chart.type == 'line')
					{
						if (series.name == "heap" || series.name == "anon")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (lineNo < 6)
						{
							series.visible = true;
						}
					}
				}
				else if (document.form.result_type.value == 'resultLSM') {
					if (options.chart.type == 'line')
					{
						if (series.name == "heap" || series.name == "anon")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (lineNo < 6)
						{
							series.visible = true;
						}
					}
				}
				else if (document.form.result_type.value == 'resultSYSTEMSERVICE') {
					if (options.chart.type == 'line')
					{
						if (series.name == "heap" || series.name == "anon")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (lineNo < 6)
						{
							series.visible = true;
						}
					}
				}
				else if (document.form.result_type.value == 'resultMaliitServer') {
					if (options.chart.type == 'line')
					{
						if (series.name == "heap" || series.name == "anon")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (lineNo < 6)
						{
							series.visible = true;
						}
					}
				}
				else if (document.form.result_type.value == 'resultWebAppMgr') {
					if (options.chart.type == 'line')
					{
						if (series.name == "heap" || series.name == "anon")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (lineNo < 6)
						{
							series.visible = true;
						}
					}
				} else if (document.form.result_type.value == 'resultstat') {
					if (options.chart.type == 'line')
					{
						if (series.name == "ctxt")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (lineNo < 2)
						{
							series.visible = true;
						}
					}
				} else if (document.form.result_type.value == 'resultpage') {
					if (options.chart.type == 'line')
					{
						if (series.name == "pgmajfault")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (series.name == "pgmajfault")
						{
							series.visible = true;
						}
					}
				} else if (document.form.result_type.value == 'resultcpuusage') {
					if (options.chart.type == 'line')
					{
						if (series.name == "USER")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						options.plotOptions.column.stacking = 'percent';
						options.tooltip.pointFormat = '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>';	
    
						if (series.name == "USER" || series.name == "NICEUSER" || series.name == "SYSTEM" || series.name == "IDLE")
						{
							series.visible = true;
						}
					}
				} else if (document.form.result_type.value == 'resultcpuvariationusage') {
					if (options.chart.type == 'line')
					{
						if (series.name == "USER")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						options.plotOptions.column.stacking = 'percent';
						options.tooltip.pointFormat = '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.percentage:.0f}%)<br/>';	
    
						if (series.name == "USER" || series.name == "NICEUSER" || series.name == "SYSTEM" || series.name == "IDLE")
						{
							series.visible = true;
						}
					}
				} else if (document.form.result_type.value == 'resultdiskstat') {
					if (options.chart.type == 'line')
					{
						if (series.name == "R_TOTAL")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (series.name == "R_TOTAL")
						{
							series.visible = true;
						}
					}
				} else if (document.form.result_type.value == 'resultionheap') {
					if (options.chart.type == 'line')
					{
						if (series.name == "   total")
						{
							series.visible = true;
						}
					} else if (options.chart.type == 'column') {
						if (series.name == "   total")
						{
							series.visible = true;
						}
					}
				}
				options.series.push(series);
			}
		});
		//putting all together and create the chart
		var chart = new Highcharts.Chart(options);
	});         
});
</script>
<body>

<form name="form" method="get">
<select id="user_id" onchange="ResultSelect('user');">
<?php
	foreach($user_list as $item) {
?>
		<option value="<?php echo $item?>"><?php echo $item ?></option>
<?php
	}
?>
</select>

<select id="result_id" onchange="ResultSelect('result');">
<?php
	foreach($result_list as $item) {
?>
		<option value="<?php echo $item?>"><?php echo $item ?></option>
<?php
	}
?>
</select>

<select id="result_type" onchange="ResultSelect('result');">
<option value="resultmeminfo">MEMINFO</option>
<option value="resultpss">PSS</option>
<option value="resultstat">STAT</option>
<option value="resultpage">PAGE</option>
<option value="resultcpuusage">CPU_ACCU</option>
<option value="resultcpuvariationusage">CPU_VARI</option>
<option value="resultdiskstat">DISK</option>
<option value="resultionheap">IONHEAP</option>
<option value="">--------------------</option>
<option value="resultQtWebProcess">QtWebProcess</option>
<option value="resultWebAppMgr">WebAppMgr</option>
<option value="resultLSM">LSM</option>
<option value="resultSYSTEMSERVICE">SYSTEMSERVICE</option>
<option value="resultMaliitServer">MaliitServer</option>
</select>
<select id="chart_type" onchange="ResultSelect('result');">
<option value="line">Line</option>
<option value="column">Stacked Column</option>
</select>
<script language="javascript">
	document.form.user_id.value= '<?php echo $user_id?>';
	document.form.result_id.value= '<?php echo $result_id?>';
	document.form.result_type.value= '<?php echo $result_type?>';
	document.form.chart_type.value= '<?php echo $chart_type?>';
</script>
</form>

<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<div id="container" style="min-width: 310px; height: 650px; margin: 0 auto"></div>

<?php include 'footer.php';?>
</body>
</html>
