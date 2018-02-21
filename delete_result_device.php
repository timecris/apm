<?php include 'common.php';?>
<?php include 'session.php';?>
<?php
	$conn = connectDevice($type, $userid, $password, $hostname, $port);
	if (!$conn) {
		die("Device is not running\n");
	}

	ssh_exec($conn, "mount -o rw,remount /");
	ssh_exec($conn, "rm -Rf ". $DEVICE_RESULT_DIR);
	echo "<b><h1>Deleting old result data..Done</h1></b>";
	
	if ($conn) {
		disconnectDevice($conn);
	}
?>
