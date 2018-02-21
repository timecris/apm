<script>
	var meminit = 0, cpuinit = 0, vminit = 0;
	var memchart = 0, cpuchart = 0, vmchart = 0; 
	var memcurcnt = 0, cpucurcnt = 0, vmcurcnt = 0;
	var conn = '<?php echo $conn_ret ?>';

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
				parseGlobalCpu(parse_str[1]);
				parseVmstat(parse_str[2]);

				if (meminit == 0) {
					//Available Memory
					if (memchart.series[2]) {
						memchart.series[2].visible = true;
						memchart.legend.update();
					}
					meminit = 1;
				}
				if (vminit == 0) {
					//allocstall
					if (vmchart.series[69]) {
						vmchart.series[69].visible = true;
						vmchart.legend.update();
					}
					vminit = 1;
				}
			},
			cache: false
		});
	}

	function parseMemory(mem_string) {
		var strArray = String(mem_string).split(',');

		//type casting
		var time = (new Date()).getTime();
		//var time = strArray[0] * 1;	//time
		var count = strArray[0] * 1;	//count of item
		var base = 1;			//base index of array
		var chart = memchart;

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
			chart.series[exist].addPoint([time, strArray[base+i+count]], true, false, false);
		}
		// call it again after one second
		setTimeout(requestData, <?php echo $sampling_rate*1000?>);
	}

	function parseGlobalCpu(cpu_string) {
		if (!cpu_string) {
			return;
		}
		var strArray = String(cpu_string).split(',');

		//type casting
		var time = (new Date()).getTime();
		//var time = strArray[0] * 1;	//time
		var count = strArray[0] * 1;	//count of item
		var base = 1;			//base index of array
		var chart = cpuchart;
		var sum = 0;

		for (var i = 0; i < count; i++) {
			var exist = -1;
			for (var j = 0; j < cpucurcnt; j++) {
				if (chart.series[j].name == strArray[base+i]) {
					exist = j;
					break;
				}
			}
			if (exist == -1) {
				if (strArray[base+i] == "Idle") {
					chart.addSeries({
						name: strArray[base+i],
						data: [],
						visible: true,
						color: '#f2f2f2'
					}, false, false);
				} else {
					chart.addSeries({
						name: strArray[base+i],
						data: [],
						visible: true,
					}, false, false);
				}
				exist = cpucurcnt;
				cpucurcnt++;
			}
			//type casting
			strArray[base+i+count] *= 1;
			if (chart.series[exist].name != "Idle") {
				sum += strArray[base+i+count];
			}
			chart.series[exist].addPoint([time, strArray[base+i+count]], true, false, false);
		}
		chart.setTitle({ text: "Cpu Usage " + sum + "%"});
	}

	function parseVmstat(vm_string) {
		if (!vm_string) {
			return;
		}

		//alert(vm_string);
		var strArray = String(vm_string).split(',');

		//type casting
		var time = (new Date()).getTime();
		//var time = strArray[0] * 1;	//time
		var count = strArray[0] * 1;	//count of item
		var base = 1;			//base index of array
		var chart = vmchart;

		for (var i = 0; i < count; i++) {
			var exist = -1;
			for (var j = 0; j < vmcurcnt; j++) {
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
				exist = vmcurcnt;
				vmcurcnt++;
			}
			//type casting
			strArray[base+i+count] *= 1;
			chart.series[exist].addPoint([time, strArray[base+i+count]], true, false, false);
		}
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
					text: 'Mbytes',
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
				text: 'CPU Usage (%)',
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
			series: []
		});

		vmchart = new Highcharts.Chart({
			chart: {
				renderTo: 'vmcontainer',
				defaultSeriesType: 'spline'
			},
			title: {
				text: 'Virtual Memory Statistics',
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
					text: '',
					margin: 80
				}
			},

			tooltip: {
				shared: true
			},
			plotOptions: {
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

	});
</script>
