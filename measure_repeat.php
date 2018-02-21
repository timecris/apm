<?php include 'common.php';?>
<?php include 'session.php';?>
<html>
<body>
<?php	
	$times = isset($_GET['times']) ? $_GET['times'] : 1;
	$idle_time = isset($_GET['idle_time']) ? $_GET['idle_time'] : $DEFAULT_IDLE_TIME;
	$launch_interval = isset($_GET['launch_interval']) ? $_GET['launch_interval'] : $DEFAULT_LAUNCH_INTERVAL;

	//if exist old test configuration, it should be removed. 
	//this is just one time test
	$cmd = array(
		"mount -o rw,remount /",		
		"echo " . $times . " > " . $DEVICE_MEASURE_REPEAT_ENABLED,
		"echo " . $idle_time . " >> " . $DEVICE_MEASURE_REPEAT_ENABLED,
		"echo " . $launch_interval . " >> " . $DEVICE_MEASURE_REPEAT_ENABLED,
		"echo /dev/null > /var/log/messages",
		"echo /dev/null > /var/log/kernel.log",
		"sync",
		"/sbin/reboot"
	);

	$conn = connectDevice($type, $userid, $password, $hostname, $port);
	if (!$conn) {
		die("Device is not running\n");
	}

	foreach($cmd as $item) {
		echo "Command : <b>" . $item . "</b>";

		$stream = ssh2_exec($conn, $item);
		stream_set_blocking($stream, true);

		$stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
		$output = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
		print "<pre>".stream_get_contents($stream_out)."</pre>";
		echo "\n\n";
	}

	echo "<b><h1>Complete..!!</h1></b>";
	
	if ($conn) {
		//already closed??
		disconnectDevice($conn);
	}
?>
</body>
</html>