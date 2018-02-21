<?php include 'common.php';?>
<?php include 'session.php';?>
<?php		
	if ($conn_ret == "") {
		die("Device is not connected");
	}
	$source_files = array(
		$SERVER_DIR . DS . "sh" . DS . "measure.sh",
		$SERVER_DIR . DS . "sh" . DS . "parser.sh",
		$SERVER_DIR . DS . "sh" . DS . "realtime_meminfo.sh",
		$SERVER_DIR . DS . "sh" . DS . "realtime_pss.sh",
		$SERVER_DIR . DS . "sh" . DS . "realtime_process.sh",
		$SERVER_DIR . DS . "sh" . DS . "webapp_list",
		$SERVER_DIR . DS . "sh" . DS . "timeout",
		$SERVER_DIR . DS . "sh" . DS . "ps",
		$SERVER_DIR . DS . "sh" . DS . "procrank",
		$SERVER_DIR . DS . "sh" . DS . "librank",
		$SERVER_DIR . DS . "sh" . DS . "bash",
		$SERVER_DIR . DS . "sh" . DS . "pmap",
		$SERVER_DIR . DS . "sh" . DS . "bonnie++",
		$SERVER_DIR . DS . "sh" . DS . "find",
		$SERVER_DIR . DS . "sh" . DS . "libprocps.so.4.0.0"
/*		$SERVER_DIR . DS . "sh" . DS . "libgcc_s.so.1",
		$SERVER_DIR . DS . "sh" . DS . "libc-2.17.so",
		$SERVER_DIR . DS . "sh" . DS . "ld-2.17.so",
		$SERVER_DIR . DS . "sh" . DS . "libproc-3.2.8.so",
		$SERVER_DIR . DS . "sh" . DS . "libdl-2.17.so",
		$SERVER_DIR . DS . "sh" . DS . "libtinfo.so.5.9",
		$SERVER_DIR . DS . "sh" . DS . "libprocps.so.4.0.0",
		$SERVER_DIR . DS . "sh" . DS . "libm-2.17.so",
		$SERVER_DIR . DS . "sh" . DS . "librt-2.17.so",
		$SERVER_DIR . DS . "sh" . DS . "libpthread-2.17.so"		
*/
	);
	$target_files = array(
		$DEVICE_INSTALL_DIR . "measure.sh",
		$DEVICE_INSTALL_DIR . "parser.sh",
		$DEVICE_INSTALL_DIR . "realtime_meminfo.sh",
		$DEVICE_INSTALL_DIR . "realtime_pss.sh",
		$DEVICE_INSTALL_DIR . "realtime_process.sh",
		$DEVICE_INSTALL_DIR . "webapp_list",
		$DEVICE_INSTALL_DIR . "timeout",
		$DEVICE_INSTALL_DIR . "ps",
		$DEVICE_INSTALL_DIR . "procrank",
		$DEVICE_INSTALL_DIR . "librank",
		$DEVICE_INSTALL_DIR . "bash",
		$DEVICE_INSTALL_DIR . "pmap",
		$DEVICE_INSTALL_DIR . "bonnie++",
		$DEVICE_INSTALL_DIR . "find",
		"/lib/libprocps.so.4.0.0"
/*
		"/lib/libgcc_s.so.1",
		"/lib/libc-2.17.so",
		"/lib/ld-2.17.so",
		"/lib/libproc-3.2.8.so",
		"/lib/libdl-2.17.so",
		"/lib/libtinfo.so.5.9",
		"/lib/libm-2.17.so",
		"/lib/librt-2.17.so",
		"/lib/libpthread-2.17.so"
*/
	);

	$conn = connectDevice($type, $userid, $password, $hostname, $port);
	if (!$conn) {
		die("Device is not running\n");
	}

	$stream = ssh2_exec($conn, "mount -o rw,remount /");
	stream_set_blocking($stream, true);

	$stream = ssh2_exec($conn, "mkdir /data");
	$stream = ssh2_exec($conn, "mkdir /lib");

	for ($index = 0 ; $index < count($source_files); $index ++) {
		echo "Source : <b>" . $source_files[$index] . "<br>";
		echo "Target : <b>" . $target_files[$index] . "<br>";
		echo "Index : <b>" . $index . "<br><br><br>";
		ssh2_scp_send($conn, $source_files[$index], $target_files[$index], 0655);
	}	

	//In order to use "pmap -X" command create symbolic link
	$stream = ssh2_exec($conn, "ln -s /lib/libprocps.so.4.0.0 /lib/libprocps.so.4");

/*
	$stream = ssh2_exec($conn, "ln -s /lib/ld-2.17.so /lib/ld-linux-armhf.so.3");
	$stream = ssh2_exec($conn, "ln -s /lib/libc-2.17.so /lib/libc.so.6");
	$stream = ssh2_exec($conn, "ln -s /lib/libdl-2.17.so /lib/libdl.so.2");
	$stream = ssh2_exec($conn, "ln -s /lib/libtinfo.so.5.9 /lib/libtinfo.so.5");
	$stream = ssh2_exec($conn, "ln -s /lib/libprocps.so.4.0.0 /lib/libprocps.so.4");
	$stream = ssh2_exec($conn, "ln -s /lib/libm-2.17.so /lib/libm.so.6");
	$stream = ssh2_exec($conn, "ln -s /lib/librt-2.17.so /lib/librt.so.1");
	$stream = ssh2_exec($conn, "ln -s /lib/libpthread-2.17.so /lib/libpthread.so.0");	
*/
?>
