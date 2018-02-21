<?php include 'common.php';?>
<?php include 'session.php';?>
<?php 
	$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : '';
	$conn = connectDevice($type, $userid, $password, $hostname, $port);
	if (!$conn) {
		die("Device is not running\n");
	}
	// Set the JSON header
	header("Content-type: text/json");

	// The x value is the current JavaScript time, which is the Unix time multiplied by 1000.
	$x = time() * 1000;
	// The y value is a random number

	$stream = ssh2_exec($conn, $cmd);
	stream_set_blocking($stream, true);

	$stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
	$output = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
	$count = stream_get_contents($stream_out);

	// Create a PHP array and echo it as JSON
	$ret = array($x, $count);
	echo json_encode($ret);
	
	if ($conn) {
		//already closed??
		disconnectDevice($conn);
	}
?>

