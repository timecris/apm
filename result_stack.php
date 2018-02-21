<?php include 'common.php';?>
<?php   
	$result_id = isset($_GET['result_id']) ? $_GET['result_id'] : '';
	$result_type = isset($_GET['result_type']) ? $_GET['result_type'] : '';

	$dh  = opendir($SERVER_DATA_DIR);
	if (!$dh) {
		die("directory not found");
	}
	$tmp = '';

	while (false !== ($filename = readdir($dh))) {
		preg_match('/^[0-9]*_*/', $filename, $match);
		$dir = is_dir($SERVER_DATA_DIR . DS . $filename);

		if($filename == "." || $filename == "..")
		{
			continue;
		}
		if ($match && $dir) {
			$files[] = $filename;
			$tmp = $filename;
		}
	}

	//Set the default value by last result_id(tmp)
	if ($result_id == '' && $tmp != '') { 
		$result_id = $tmp;
	}

	//Set the default value by last result_id(tmp)
	if ($result_type == '') { 
		$result_type = 'resultmeminfo';
	}

	closedir($dh);
?>
<!DOCTYPE HTML>
<html lang="ko">

<?php include 'header.php';?>





<script type="text/javascript">
        jQuery(document).ready(function() { 
		
            var options = {
                chart: {
					type: 'column',
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
            $.get('data/<?php echo $result_id?>/<?php echo $result_type?>', function(data) {
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
						//show only the AFM
						if (series.name == "AFM" || series.name == "QtWebProcess_PSS")
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
<select id="result_id" onchange="ResultSelect();">

<?php
	foreach($files as $item) {
?>
		<option value="<?php echo $item?>"><?php echo $item ?></option>
<?php
	}
?>

</select>

<select id="result_type" onchange="ResultSelect();">
<option value="resultmeminfo">MEMINFO</option>
<option value="resultpss">PSS</option>
</select>
<script language="javascript">
	document.form.result_id.value= '<?php echo $result_id?>';	
	document.form.result_type.value= '<?php echo $result_type?>';	
</script>
</form>

<script src="http://code.highcharts.com/highcharts.js"></script>
<script src="http://code.highcharts.com/modules/exporting.js"></script>
<div id="container" style="min-width: 310px; height: 650px; margin: 0 auto"></div>

<?php include 'footer.php';?>
</body>
</html>