<?php include 'common.php';?>
<?php include 'session.php';?>
<?php
	$conn = connectDevice($type, $userid, $password, $hostname, $port);
	if (!$conn) {
		die("Device is not running\n");
	}

	ssh_exec($conn, "echo '3' > /proc/sys/vm/drop_caches");
	echo "<b><h1>droping page cache and inode, dentry cache..Done</h1></b>";

	if ($conn) {
		disconnectDevice($conn);
	}
?>
