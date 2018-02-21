<script type="text/javascript" language="javascript">
	function PullResult() {
		if (confirm('Are you sure you want to get result data from connected device?')) {
			var user_id = prompt("Input [email ID]_[Category]. ex) sunghwan.jeon_offical3", "");
			if (user_id == 0 || user_id == '' || user_id == null)
			{
				return;
			}
			window.open("./pull_result.php?user_id="+user_id);	
		} 
	}

	function RunMeasure() {	
		if (confirm('Are you sure you want to measure on connected device?')) {
			var idle_time = prompt("Input IDLE_TIME", "");
			var launch_interval = prompt("Input app launch interva", "");
			window.open("./measure.php?idle_time="+idle_time+"&launch_interval="+launch_interval);
		} else {
		}
	}

	function Install() {
		if (confirm('Are you sure you want to install test script to device?')) {
			window.open("./install_ssh.php");	
		} else {

		}
	}

	function Dropcache() {
		window.open("./drop_cache.php");	
	}

	function MeasureRepeat() {	
		if (confirm('Are you sure you want to measure repeatedly on connected device?')) {
			var times = prompt("How many times do you want", "");
			if (times == 0 || times == '' || times == null)
			{
				return;
			}
			var idle_time = prompt("Input IDLE_TIME", "");
			if (idle_time == 0 || idle_time == '' || idle_time == null)
			{
				return;
			}
			var launch_interval = prompt("Input app launch interval", "");
			if (launch_interval == 0 || launch_interval == '' || launch_interval == null)
			{
				return;
			}
			window.open("./measure_repeat.php?times="+times+"&idle_time="+idle_time+"&launch_interval="+launch_interval);
		}
	}

	function DeleteResultDevice() {	
		if (confirm('Are you sure you want to delete result on device?')) {
			window.open("./delete_result_device.php");
		}
	}
	function RebootDevice() {	
		if (confirm('Are you sure you want to reboot connected device?')) {
			window.open("./reboot.php");
		}
	}

</script>
<!--<input type="button" value="Measure" onclick="RunMeasure();"/>-->
<input type="button" value="Measure" onclick="MeasureRepeat();"/>
<input type="button" value="Pull Result" onclick="PullResult();"/>
<input type="button" value="Delete Result(Device)" onclick="DeleteResultDevice();"/>
<input type="button" value="Install" onclick="Install();"/>
<input type="button" value="Reboot" onclick="RebootDevice();"/>
<input type="button" value="Drop Cache" onclick="request_dropcache();"/>
<input type="button" value="Memory Trim" onclick="request_memtrim();"/>
