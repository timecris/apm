<?php include 'common.php';?>
<?php
	$type = isset($_GET['type']) ? $_GET['type'] : '';
	$r_userid = isset($_POST['userid']) ? $_POST['userid'] : '';
	$r_password = isset($_POST['password']) ? $_POST['password'] : '';
	$r_port = isset($_POST['port']) ? $_POST['port'] : '';
	$r_hostname = isset($_POST['hostname']) ? $_POST['hostname'] : '';

	$conn_test = connectDevice($type, $r_userid, $r_password, $r_hostname, $r_port);

	if ($conn_test) {
		disconnectDevice($conn_test);
		createSession($type, $r_userid, $r_password, $r_port, $r_hostname);
	} 

	function createSession($type, $userid, $password, $port, $hostname) {
		$_SESSION['type'] = $type;
		$_SESSION['userid'] = $userid;
		$_SESSION['password'] = $password;
		$_SESSION['port'] = $port;
		$_SESSION['hostname'] = $hostname;
	}	
?>

<script>
	history.back(-1);
</script>
