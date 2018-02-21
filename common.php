<?php	
	//session_save_path('./sessions');
	session_start();

	$uri = "$_SERVER[REQUEST_URI]";
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	$user_ip = $_SERVER['REMOTE_ADDR'];
	$user_os        =   getOS();
	$user_browser   =   getBrowser();

	define('DS', DIRECTORY_SEPARATOR);
	
	$SERVER_IP=$_SERVER['SERVER_ADDR'];
	$SERVER_PORT=$_SERVER['SERVER_PORT'];
	$SERVER_DIR=".";
	$SERVER_DATA_DIR=$SERVER_DIR . DS . "data";
	$SERVER_SESSION_DIR=$SERVER_DIR . DS . "sessions";

	$DEFAULT_USB_DEVICE_NAME=$user_ip;
	$DEFAULT_USB_PORT="8021";
	$DEFAULT_USB_LOGIN_ID="root";
	$DEFAULT_USB_PASSWORD="";

	$DEFAULT_REMOTE_DEVICE_NAME="165.186.175.80";
	$DEFAULT_REMOTE_PORT="22";
	$DEFAULT_REMOTE_LOGIN_ID="apm";
	$DEFAULT_REMOTE_PASSWORD="";

	$DEVICE_ROOT_PUBKEY=$SERVER_DIR . DS . "key" . DS . "authorized_keys";
	$DEVICE_PRVKEY=$SERVER_DIR . DS . "key" . DS . "device.rsa";

	#$DEVICE_INSTALL_DIR="/home2/apm/bin/";
	$DEVICE_INSTALL_DIR="/tmp/apm/";
	$DEVICE_PRELOAD_CMD="LD_PRELOAD=" . $DEVICE_INSTALL_DIR . "libc-2.24.so " . $DEVICE_INSTALL_DIR . "ld-2.24.so";
	$DEVICE_PERSIST_DIR="/var/";
	$DEVICE_MEASURE_FILE=$DEVICE_INSTALL_DIR . "measure.sh";
	$DEVICE_RESULT_DIR=$DEVICE_INSTALL_DIR . "result";
	$DEVICE_PARSER_FILE=$DEVICE_INSTALL_DIR . "parser.sh";
	$DEVICE_APPLIST_FILE=$DEVICE_INSTALL_DIR . "webapp_list";
	$DEVICE_MEASURE_REPEAT_ENABLED=$DEVICE_PERSIST_DIR . "measure_enabled";

	$DEFAULT_IDLE_TIME=30;
	$DEFAULT_LAUNCH_INTERVAL=10;

	function getOS() { 
		global $user_agent;
		$os_platform    =   "Unknown OS Platform";
		$os_array       =   array(
								'/windows nt 10/i'     =>  'Windows 10',
								'/windows nt 6.3/i'     =>  'Windows 8.1',
								'/windows nt 6.2/i'     =>  'Windows 8',
								'/windows nt 6.1/i'     =>  'Windows 7',
								'/windows nt 6.0/i'     =>  'Windows Vista',
								'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
								'/windows nt 5.1/i'     =>  'Windows XP',
								'/windows xp/i'         =>  'Windows XP',
								'/windows nt 5.0/i'     =>  'Windows 2000',
								'/windows me/i'         =>  'Windows ME',
								'/win98/i'              =>  'Windows 98',
								'/win95/i'              =>  'Windows 95',
								'/win16/i'              =>  'Windows 3.11',
								'/macintosh|mac os x/i' =>  'Mac OS X',
								'/mac_powerpc/i'        =>  'Mac OS 9',
								'/linux/i'              =>  'Linux',
								'/ubuntu/i'             =>  'Ubuntu',
								'/iphone/i'             =>  'iPhone',
								'/ipod/i'               =>  'iPod',
								'/ipad/i'               =>  'iPad',
								'/android/i'            =>  'Android',
								'/blackberry/i'         =>  'BlackBerry',
								'/webos/i'              =>  'Mobile'
							);
		foreach ($os_array as $regex => $value) { 
			if (preg_match($regex, $user_agent)) {
				$os_platform    =   $value;
			}
		}   
		return $os_platform;
	}

	function getBrowser() {
		global $user_agent;
		$browser        =   "Unknown Browser";
		$browser_array  =   array(
								'/msie/i'       =>  'Internet Explorer',
								'/firefox/i'    =>  'Firefox',
								'/safari/i'     =>  'Safari',
								'/chrome/i'     =>  'Chrome',
								'/opera/i'      =>  'Opera',
								'/netscape/i'   =>  'Netscape',
								'/maxthon/i'    =>  'Maxthon',
								'/konqueror/i'  =>  'Konqueror',
								'/mobile/i'     =>  'Handheld Browser'
							);
		foreach ($browser_array as $regex => $value) { 
			if (preg_match($regex, $user_agent)) {
				$browser    =   $value;
			}
		}
		return $browser;
	}

	function getPort() {
		global $DEFAULT_USB_PORT;

		$ret = exec('novacom -l');

		if ($ret == '') {
			echo "Novacom USB is not connected";
		} else {

			//expected string is "58961 ddfb4b13d9b0df0580d1e56ea8393b1c7d587096 usb0 w2-linux 58962:10022 root"
			$pattern = " ";
			$arrTmp = explode($pattern,$ret);
			//expected arrTmp[4] is "58962:10022"
			$pattern = ":";
			$arrStr = explode($pattern, $arrTmp[4]);
			$DEFAULT_USB_PORT = $arrStr[0];
			//seperate developer or root
			//$dev_id = $arrTmp[5];
		}
	}

	function connectDevice($type, $userid, $password, $hostname, $port) {
		global $DEVICE_ROOT_PUBKEY;
		global $DEVICE_PRVKEY;

		$conn = NULL;

		if (empty($userid) || empty($hostname) || empty($port)) {
			return NULL;
		}

		if ($type == 'proxy') {
			$conn = ssh2_connect($hostname, $port);
			if ($conn){
				ssh2_auth_password($conn, "root", "");
				#Connect to device with uid 0(root), so private key is needed.
				//ssh2_auth_pubkey_file($conn, $userid, $DEVICE_ROOT_PUBKEY, $DEVICE_PRVKEY);
			}
		} else if ($type == 'direct') {
			$conn = ssh2_connect($hostname, $port);
			if ($conn){
				ssh2_auth_password($conn, $userid, $password);
			}
		}
		return $conn;
	}

	function disconnectDevice($conn_test) {
		if ($conn_test) {
			ssh2_exec($conn_test, "exit");
		}
	}
	
	function getProcessList() {
		global $type;
		global $userid;
		global $password;
		global $hostname;
		global $port;
		global $DEVICE_INSTALL_DIR;
		global $DEVICE_PRELOAD_CMD;

		$conn = connectDevice($type, $userid, $password, $hostname, $port);
		if (!$conn) {
			//echo("Device is not running\n");	
			return;
		}
		$cmd=$DEVICE_PRELOAD_CMD . " " . $DEVICE_INSTALL_DIR . "ps aux --sort -rss | grep -Ev \"bash|sh|login|grep|procrank|vbtd|awk|dropbear|agetty|IDVerifierPluginHMI|IdentityManager|micommanager|dlt-cdh|cedmd|\[\" | awk '{print $11}' ";
		//$stream = ssh2_exec($conn, $DEVICE_PRELOAD_CMD . " " . $DEVICE_INSTALL_DIR . "ps aux --sort -rss | grep -v \"\[\" | awk '{print $11}' ");
		//echo $cmd;
		$stream = ssh2_exec($conn, $cmd);
		
		stream_set_blocking($stream, true);	
		$stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
		$output = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
		//print "<pre>".stream_get_contents($stream_out)."</pre>";
		$str = stream_get_contents($stream_out);
	//	echo $str;
		if ($conn) {
			disconnectDevice($conn);
		}
		return $str;
	}

	function unserialize_php($session_data) {
		$return_data = array();
		$offset = 0;
		while ($offset < strlen($session_data)) {
			if (!strstr(substr($session_data, $offset), "|")) {
				throw new Exception("invalid data, remaining: " . substr($session_data, $offset));
			}
			$pos = strpos($session_data, "|", $offset);
			$num = $pos - $offset;
			$varname = substr($session_data, $offset, $num);
			$offset += $num + 1;
			$data = unserialize(substr($session_data, $offset));
			$return_data[$varname] = $data;
			$offset += strlen(serialize($data));
		}
		return $return_data;
	}

	function getDirectoryItemList($path) {
		$dh  = opendir($path);
		if (!$dh) {
			die("directory not found");
		}

		while ($readdir[] = readdir($dh));
		sort($readdir);
		closedir($dh);

		$dirlist = array();

		foreach ($readdir as $dirname) {
			//preg_match('/^[0-9]*_*/', $filename, $match);
			$dir = is_dir($path . DS . $dirname);

			if($dirname == "." || $dirname == ".." || $dirname == "")
			{
				continue;
			}
			if ($dir) {
				$dirlist[] = $dirname;
			}
		}
		return $dirlist;
	}


	/* Not USED */
	function getServerSessionIpAddrList() {
		global $SERVER_SESSION_DIR;
				
		$dh  = opendir($SERVER_SESSION_DIR);
		if (!$dh) {
			die("directory not found");
		}
		$tmp = '';

		while ($readdir[] = readdir($dh));
		closedir($dh);

		$remote_ipaddr = array();

		foreach ($readdir as $filename) {
			if($filename == "." || $filename == "..")
			{
				continue;
			}
			
			$session_data = file_get_contents($SERVER_SESSION_DIR . DS .  $filename);
			if (!$session_data) {
				continue;
			}
			$return_data = unserialize_php($session_data);
			$ipaddr = $return_data["hostname"];
			if ($ipaddr == $user_ip) {
				continue;
			}
			//check if that already exist in array
			if (in_array($ipaddr, $remote_ipaddr)) {
				    echo "Got ". $ipaddr;
					continue;
			}

			if (!filter_var($ipaddr, FILTER_VALIDATE_IP) == false) {
				$remote_ipaddr[] = $ipaddr;
			}
		}
		return $remote_ipaddr;
	}

	function ssh_exec($conn, $cmd) {
		if (!$conn || !$cmd) {
			return false;
		}
		$stream = ssh2_exec($conn, $cmd);
		return $stream;
	}

	function ssh_print($stream) {
		stream_set_blocking($stream, true);
		$stream_out = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);
		$output = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
		$contents = stream_get_contents($stream_out);
		print "<pre>".$contents."</pre>";
		flush();
	}
?>
