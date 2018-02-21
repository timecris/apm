<?php include 'common.php';?>
<?php   
	$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
	$result_id_1 = isset($_GET['result_id_1']) ? $_GET['result_id_1'] : '';
	$result_id_2 = isset($_GET['result_id_2']) ? $_GET['result_id_2'] : '';
	$result_type = isset($_GET['result_type']) ? $_GET['result_type'] : '';

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
		if ($result_id_1 == '' && $result_id_2 == '' && count($result_list) > 1) {
			$result_id_1 = $result_list[0];
			$result_id_2 = $result_list[1];
		}
	}

	if ($result_type == '') 
		$result_type = 'resultmeminfo';
?>
<!DOCTYPE HTML>
<html lang="ko">
<?php include 'header.php';?>
<script type="text/javascript" language="javascript">
function ResultSelect(v) {
	var user_id = document.getElementById('user_id').value;
	if (v == 'user') {
		var id_1 = '';
		var id_2 = '';
	} else if (v == 'result') {
		var id_1 = document.getElementById('result_id_1').value;
		var id_2 = document.getElementById('result_id_2').value;
	}
	var type = document.getElementById('result_type').value;
	location.href = './comparison.php?user_id='+user_id+'&result_id_1='+id_1+'&result_id_2='+id_2+'&result_type='+type;
}
</script>

<script type="text/javascript">
        jQuery(document).ready(function() {
            var options = {
                chart: {
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
                        text: 'MiB'
                    }
                },
				plotOptions: {
			        line: {
				        events: {
							legendItemClick: function () {
								var seriesIndex = this.index;
								var series = this.chart.series;
								var series_name = this.chart.series[seriesIndex].name;

								for (var i = 0; i < series.length; i++)
								{
									if (series[i].index != seriesIndex && series[i].name == series_name)
									{
										series[i].visible ?
										series[i].hide() :
										series[i].show();
									} 
								}
							}
						},
					    showInLegend: true,
			        }
				},
                series: []
            };
            // JQuery function to process the csv data
            $.get('data/<?php echo $user_id?>/<?php echo $result_id_1?>/<?php echo $result_type?>', function(data) {
                // Split the lines
                var lines = data.split('\n');
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
						//show only the AFM or Qt
						if (series.name == "AFM" || series.name == "QtWebProcess" || series.name == "LAUNCHTIME"
							|| series.name == "ctxt" || series.name == "pgpgin" || series.name == "USER" || series.name == "R_TOTAL" 
							|| series.name == "   total")
						{
							series.visible = true;
						}     
                        options.series.push(series); 
                    }                     
                });
                //putting all together and create the chart
                var chart = new Highcharts.Chart(options);
            });

            // JQuery function to process the csv data
            $.get('data/<?php echo $user_id?>/<?php echo $result_id_2?>/<?php echo $result_type?>', function(data) {
                // Split the lines
                var lines = data.split('\n');
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
							dashStyle: 'longdash',
                        };
                        $.each(items, function(itemNo, item) {
                            if (itemNo == 0) {
                                series.name = item;
                            } else {
                                series.data.push(parseFloat(item));
                            }
                        });				
						//show only the AFM or Qt
						if (series.name == "AFM" || series.name == "QtWebProcess" || series.name == "LAUNCHTIME"
							|| series.name == "ctxt" || series.name == "pgpgin" || series.name == "USER" || series.name == "R_TOTAL" 
							|| series.name == "   total")
						{
							series.visible = true;
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

<select id="result_id_1" onchange="ResultSelect('result');">
<?php
	foreach($result_list as $item) {
?>
		<option value="<?php echo $item?>"><?php echo $item ?></option>
<?php
	}
?>
</select>

<select id="result_id_2" onchange="ResultSelect('result');">
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
<option value="resultlaunchtime">LAUNCH TIME</option>
<option value="resultstat">STAT</option>
<option value="resultpage">PAGE</option>
<option value="resultcpuusage">CPU_ACCU</option>
<option value="resultcpuvariationusage">CPU_VARI</option>
<option value="resultdiskstat">DISK</option>
<option value="resultionheap">IONHEAP</option>

</select>

<script language="javascript">
	document.form.user_id.value= '<?php echo $user_id?>';
	document.form.result_id_1.value= '<?php echo $result_id_1?>';
	document.form.result_id_2.value= '<?php echo $result_id_2?>';
	document.form.result_type.value= '<?php echo $result_type?>';
</script>
</form>

<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<div id="container" style="min-width: 310px; height: 650px; margin: 0 auto"></div>
<?php include 'footer.php';?>
</body>
</html>
