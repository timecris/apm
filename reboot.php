<?php include 'common.php';?>
<?php include 'session.php';?>
<html>
<body>
<?php	
	$cmd = array(
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