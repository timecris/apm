<?php include 'common.php';?>
<?php include 'session.php';?>
<?php	
	if ($conn_ret == "") {
		die("Device is not connected");
	}

	$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
	if ($user_id == "") {
		die("wrong access");
	}

	$server_cmd = array(
		"rm " . $SERVER_DATA_DIR . DS . "*.tar.gz",
		"rm " . $SERVER_DATA_DIR . DS . "*.tar",
		"mkdir " . $SERVER_DATA_DIR,
		"mkdir " . $SERVER_DATA_DIR . DS . $user_id
	);

	$conn = connectDevice($type, $userid, $password, $hostname, $port);
	if (!$conn) {
		die("Device is not running\n");
	}

	$stream = ssh_exec($conn, "ls /data/result");
	stream_set_blocking($stream, true);
	$stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
	$output = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
	$contents = stream_get_contents($stream_out);
	if (!$contents) {
		die("There is no result data on " . $DEVICE_RESULT_DIR . ". First try to measure on device");
	}

	foreach($server_cmd as $item) {
		echo "<br>Command on server : <b>" . $item . "</b><br>";
		$stream = exec($item);
		echo "\n\n";
		flush();
	}

	ssh_exec($conn, "mount -o rw,remount /");
	ssh_exec($conn, "cd " . $DEVICE_RESULT_DIR . "; rm *.tar.gz");
	ssh_exec($conn, "cd " . $DEVICE_RESULT_DIR . "; rm *.tar");

	echo "<br><b><h1>Analyzing the data is in progress..It takes 1 minute per measured data.</h1></b><br>";
	flush();
		
	$stream = ssh_exec($conn, $DEVICE_PARSER_FILE . " " . $DEVICE_APPLIST_FILE);
	ssh_print($stream);
	echo "<b><h1>Done.</h1></b>";

	ssh_exec($conn, "cd " . $DEVICE_RESULT_DIR . "; for i in *; do tar cfz \$i.tar.gz \$i; done; tar cfz packed.tar.gz  *.tar.gz");

	$result = ssh2_scp_recv($conn, $DEVICE_RESULT_DIR . DS . "packed.tar.gz", $SERVER_DATA_DIR . DS . $user_id . DS . "packed.tar.gz");
	if ($result == false) {
		die("<br><b><h1>Failed to download packed file</h1></b><br>");
	}

	if (!file_exists($SERVER_DATA_DIR . DS . $user_id . DS . "packed.tar.gz")) {
		die("<br><b><h1>Failed to download packed file. not exist.</h1></b><br>");
	}

	echo "<b><h1>Download the packed data.</h1></b>";

	// decompress from gz
	$p = new PharData($SERVER_DATA_DIR . DS . $user_id . DS . "packed.tar.gz");
	$p->decompress(); // creates files.tar

    $phar = new PharData($SERVER_DATA_DIR . DS . $user_id . DS . "packed.tar");
    $phar->extractTo($SERVER_DATA_DIR . DS . $user_id, null, true); // extract all files	


	$dh  = opendir($SERVER_DATA_DIR . DS . $user_id);
	if (!$dh) {
		die("directory not found");
	}				    
	while (false !== ($filename = readdir($dh))) {
		preg_match('/^[0-9]*_[0-9]*.tar.gz/', $filename, $match);

		if ($match) {
			echo $filename . "<br>";
			$p = new PharData($SERVER_DATA_DIR . DS . $user_id . DS . $filename);
			$p->decompress();

			$phar = new PharData($SERVER_DATA_DIR . DS . $user_id . DS . $filename);
			$phar->extractTo($SERVER_DATA_DIR . DS . $user_id, null, true);
		}
	}
	echo "<b><h1>Decompress the packed data.</h1></b>";

	exec("rm -Rf " . $SERVER_DATA_DIR . DS . $user_id . DS . "*.tar.gz");
	exec("rm -Rf " . $SERVER_DATA_DIR . DS . $user_id . DS . "*.tar");

	echo "<b><h1>Delete downloaded zipped files</h1></b>";

	ssh_exec($conn, "cd " . $DEVICE_RESULT_DIR . "; rm *.tar.gz");
	ssh_exec($conn, "cd " . $DEVICE_RESULT_DIR . "; rm *.tar");

	echo "<b><h1>Done.</h1></b>";
	if ($conn) {
		disconnectDevice($conn);
	}
?>
