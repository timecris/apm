<?php
	$type = isset($_SESSION['type']) ? $_SESSION['type'] : '';
	$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : '';
	$password = isset($_SESSION['password']) ? $_SESSION['password'] : '';
	$port = isset($_SESSION['port']) ? $_SESSION['port'] : '';
	$hostname = isset($_SESSION['hostname']) ? $_SESSION['hostname'] : '';
	
	$conn_ret="";
	if ($type && $userid && $port) {
		$conn_ret=testConnectBySessionId();
	} else { 
		//$remote_ipaddr=getServerSessionIpAddrList();
		//getPort();
	}

	function testConnectBySessionId() {
		global $type;
		global $userid;
		global $password;
		global $port;
		global $hostname;

		$conn_test = connectDevice($type, $userid, $password, $hostname, $port);
		if ($conn_test) {
			disconnectDevice($conn_test);
			return true;			
		} else {
			session_unset();
			session_destroy();

			//if connected devices is novacom, call getPort()
			//getPort();
			return false;
		}
	}
?>
