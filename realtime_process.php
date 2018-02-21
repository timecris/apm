<script>
	var meminit = 0, cpuinit = 0;
	var memchart = 0, cpuchart = 0; 
	var memcurcnt = 0, cpucurcnt = 0;
	var conn = '<?php echo $conn_ret ?>';
	var typestr="";
	if ('<?php echo $result_type?>' == 'resulthmiapp') {
		typestr="HMIAPP"
	} else {
		typestr="System Service"
	}

	function changeType() {
		var ctype = document.getElementById('chart_type').value;
		for (var j=0; j<memcurcnt; j++) {
			memchart.series[j].update({
				type: ctype
			});
		}
	}

	function requestData() {
		if (conn == '') {
			return;
		}
		$.ajax({
			url: 'live-server-data.php?result_type=<?php echo $result_type?>',
			//[count],[item#1],[item#2],[item#3],[value#1],[value#2],[value#3]
			success: function(point) {
				//alert(point);
				var parse_str = String(point).split('|');
				parseMemory(parse_str[0]);
				parseCpu(parse_str[1]);

				if (meminit == 0) {
					for (i=0; i<1; i++) {
						if (memchart.series[i]) {
							memchart.series[i].visible = true;
						}
					}	
					memchart.legend.update();
					meminit = 1;
				}
			},
			cache: false
		});
	}

	function parseMemory(mem_string) {
		var strArray = String(mem_string).split(',');

		//type casting
		var time = (new Date()).getTime();
		var count = strArray[0] * 1;	//count of item
		var base = 1;			//base index of array
		var chart = memchart;
		var sum = 0;

		for (var i = 0; i < count; i++) {
			var exist = -1;
			for (var j = 0; j < memcurcnt; j++) {
				if (chart.series[j].name == strArray[base+i]) {
					exist = j;
					break;
				}
			}
			if (exist == -1) {
				chart.addSeries({
					name: strArray[base+i],
					data: [],
					visible: false,
				}, false, false);
				exist = memcurcnt;
				memcurcnt++;
			}
			//type casting
			strArray[base+i+count] *= 1;
			sum += strArray[base+i+count];
			chart.series[exist].addPoint([time, strArray[base+i+count]], true, false, false);
		}
		chart.setTitle({ text: typestr + " Memory Information<br>Total PSS : <b>" + numberWithCommas(parseInt(sum)) + "</b> Kbytes"});
		// call it again after one second
		setTimeout(requestData, <?php echo $sampling_rate*1000?>);
	}

	function parseCpu(cpu_string) {
		//alert(cpu_string);
		var strArray = String(cpu_string).split(',');

		var time = (new Date()).getTime();
		var count = strArray[0] * 1;	//count of item
		var base = 1;			//base index of array
		var chart = cpuchart;
		var usage = 100;

		if (cpuinit == 0) {
			chart.addSeries({
				name: "-",
				data: [],
				visible: true,
				color: '#FFFFFF'
			}, false, false);
			cpucurcnt++;
			cpuinit=1;
		}
		for (var i = 0; i < count; i++) {
			var exist = -1;
			for (var j = 0; j < cpucurcnt; j++) {
				if (chart.series[j].name == strArray[base+i]) {
					exist = j;
					break;
				}
			}
			if (exist == -1) {
				chart.addSeries({
					name: strArray[base+i],
					data: [],
					visible: false,
				}, false, false);
				exist = cpucurcnt;
				cpucurcnt++;
			}
			//type casting
			strArray[base+i+count] *= 1;
			if (strArray[base+i+count] != 0) {
				usage -= strArray[base+i+count];
				chart.series[exist].visible = true;
			}
			chart.series[exist].addPoint([time, strArray[base+i+count]], true, false, false);
 		}
		chart.series[0].addPoint([time, usage], true, false, false);
		chart.setTitle({ text: typestr + "<br>Total CPU Usage <b>" + parseInt(100-usage) + "%</b>"});
	}

	$(document).ready(function() {
		memchart = new Highcharts.Chart({
			chart: {
				renderTo: 'container',
				defaultSeriesType: '<?php echo $chart_type?>',
				events: {
					load: requestData
				}
			},
			title: {
				text: 'Memory Information',
				style: {
					fontSize: '30px'
				}
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
					text: 'Kbytes',
					style: {
						fontSize: '20px'
					},
					margin: 10
				}
			},

			tooltip: {
				shared: true
			},
			plotOptions: {
				column: {
					animation: false,
					stacking: 'normal',
					dataLabels: {
						enabled: true,
						color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
						style: {
							textShadow: '0 0 3px black'
						}
					}
				},
				spline: {
					animation: false,
					lineWidth: 4,
					states: {
						hover: {
							lineWidth: 5
						}
					},
					marker: {
						enabled: false
					},
				},
			},
			series: []
		});

		cpuchart = new Highcharts.Chart({
			chart: {
				renderTo: 'cpucontainer',
				defaultSeriesType: 'area',
				events: {
					//load: requestData
				}
			},
			title: {
				text: 'CPU Usage',
				style: {
					fontSize: '30px'
				}
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
					text: '%',
					style: {
						fontSize: '20px'
					},
					margin: 10
				}
			},

			tooltip: {
				shared: true
			},

			plotOptions: {
				area: {
					stacking: 'percent',
					lineColor: '#666666',
					lineWidth: 0,
					marker: {
						enabled: false,
						symbol: 'circle',
						radius: 2,
						states: {
							hover: {
								enabled: true
							}
						}
					}
				}
			},
		});

	});
</script>
