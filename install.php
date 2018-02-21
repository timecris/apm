<?php include 'common.php';?>
<?php include 'session.php';?>
<?php	
	if ($conn_ret == "") {
		die("Device is not connected");
	}

	$cmd = array(
		"ares-shell -r \"mount -o rw,remount /\"",
		"ares-push ." . DS . "sh" . DS . "measure.sh " . $DEVICE_INSTALL_DIR,
		"ares-push ." . DS . "sh" . DS . "parser.sh " . $DEVICE_INSTALL_DIR,
		"ares-push ." . DS . "sh" . DS . "realtime_meminfo.sh " . $DEVICE_INSTALL_DIR,
		"ares-push ." . DS . "sh" . DS . "realtime_pss.sh " . $DEVICE_INSTALL_DIR,
		"ares-push ." . DS . "sh" . DS . "realtime_process.sh " . $DEVICE_INSTALL_DIR,
		"ares-push ." . DS . "sh" . DS . "webapp_list " . $DEVICE_INSTALL_DIR,
		"ares-push ." . DS . "sh" . DS . "webos-jail.conf /etc/init/webos-jail.conf",
		"ares-push ." . DS . "ipk" . DS . "procps_3.3.10-r0_w2.ipk " . $DEVICE_INSTALL_DIR,
		"ares-push ." . DS . "sh" . DS . "timeout " . $DEVICE_INSTALL_DIR,
		"ares-shell -r \"opkg install " . $DEVICE_INSTALL_DIR . "procps_3.3.10-r0_w2.ipk \"",
		"ares-push ." . DS . "sh" . DS . "storage.sh /etc/udev/scripts/"
	);

	foreach($cmd as $item) {
		echo "Command : <b>" . $item . "</b><br><br>";

		$Handle = popen($item, "r");
		while ( !feof( $Handle ) ) { 
			$buffer = fread( $Handle, 2048 ); 
			echo $buffer; 
			echo "<br><br>";
		}
		pclose($Handle);
	}
	echo "<b><h1>Complete..!!</h1></b>";

?>

