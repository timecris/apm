<?php include 'common.php';?>
<?php include 'session.php';?>
<?php 
	$result_type = isset($_GET['result_type']) ? $_GET['result_type'] : '';

	//set default result type
	if ($result_type == '') 
		$result_type = 'resultglobal';

	$conn = connectDevice($type, $userid, $password, $hostname, $port);
	if (!$conn) {
		die("Device is not running\n");
	}
	// Set the JSON header
	header("Content-type: text/json");

	if ($result_type == 'resultglobal') {
		$stream = ssh2_exec($conn, $DEVICE_INSTALL_DIR . "realtime_global.sh");
	} else if ($result_type == 'resulthmiapp') {
		$stream = ssh2_exec($conn, $DEVICE_INSTALL_DIR . "realtime_proc.sh -E app/hmiapp");
	} else if ($result_type == 'resultsystem') {
		$stream = ssh2_exec($conn, $DEVICE_INSTALL_DIR . "realtime_proc.sh -Ev 'bash|sh|login|grep|procrank|vbtd|awk|dropbear|agetty|hmiapp'");
	} else if ($result_type == 'resultvmstat') {
		$stream = ssh2_exec($conn, $DEVICE_INSTALL_DIR . "realtime_vmstat.sh");
	} else if ($result_type == 'memtrim') {
		$stream = ssh2_exec($conn, $DEVICE_INSTALL_DIR . "hmiapp_memtrim.sh");
	} else {
		$stream = ssh2_exec($conn, $DEVICE_INSTALL_DIR . "realtime_process.sh " . $result_type . " " . $DEVICE_INSTALL_DIR);
	}
	#echo $stream;
	stream_set_blocking($stream, true);
	
	$stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);


	$output = ssh2_fetch_stream($stream,SSH2_STREAM_STDERR);

	$str = stream_get_contents($stream_out);
	#echo $str;
	// Create a PHP array and echo it as JSON
	$ret = array($str);
	echo json_encode($ret);
	
	if ($conn) {
		//already closed??
		disconnectDevice($conn);
	}
?>
