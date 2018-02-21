<meta name="viewport" content="width=device-width, initial-scale=1">
<script language="javascript">
	function Login() {
		var type = document.login.connect_type.value;
		document.login.action='./login.php?type=' + type;
		document.login.submit();
	}
	function Logout() {
		location.href = "./logout.php";
	}
	function IpaddrSelect(v) {
			document.login.hostname.value=v;
	}
	function ConnectTypeSelect(v) {
		if (v == 'proxy') {
			document.login.hostname.value="<?php echo $DEFAULT_USB_DEVICE_NAME ?>";
			document.login.port.value="<?php echo $DEFAULT_USB_PORT ?>";
			document.login.userid.value="<?php echo $DEFAULT_USB_LOGIN_ID ?>";
			document.login.password.value="";

			//readonly
			document.login.hostname.readOnly = false;
			document.login.userid.readOnly = false;
			document.login.password.readOnly = false;
			
			//color
			document.login.hostname.style.backgroundColor = 'white';
			document.login.userid.style.backgroundColor = 'gray';
			document.login.password.style.backgroundColor = 'gray';
			document.login.port.style.backgroundColor = 'white';

			document.getElementById("explanation").style.display = 'block';
		} else if (v == 'direct') {
			document.login.hostname.value="<?php echo $DEFAULT_REMOTE_DEVICE_NAME ?>";
			document.login.port.value="<?php echo $DEFAULT_REMOTE_PORT ?>";
			document.login.userid.value="<?php echo $DEFAULT_REMOTE_LOGIN_ID ?>";
			document.login.password.value="<?php echo $DEFAULT_REMOTE_PASSWORD ?>";
		
			//readonly
			document.login.hostname.readOnly = false;
			document.login.userid.readOnly = false;
			document.login.password.readOnly = false;

			//color
			document.login.hostname.style.backgroundColor = 'white';
			document.login.userid.style.backgroundColor = 'white';
			document.login.password.style.backgroundColor = 'white';
			document.login.port.style.backgroundColor = 'white';

			document.login.password.readOnly = false;
			document.getElementById("explanation").style.display = 'none';
		}
	}
</script>
<form name="login" method="post">

<?php 
	if ($conn_ret == "") {
?>
<br>
<center>
<select id="connect_type" onchange="ConnectTypeSelect(this.value);">
<option value="proxy">Proxy</option>
<option value="direct">Direct</option>
</select>
<input name="hostname" size="15" value="">
<input name="port" size="5" value="">
<input name="userid" size="20" value="">
<input name="password" type="password" size="20" value="">
<input type="button" onClick="Login();" value="CONNECT">
</center>

<div id="explanation">
</div>
<br>
<?php		
	} else {
?>
<center>
<font size="3" color="white">Connected on <?php echo $hostname?></font>
<input type="button" onclick="Logout();" value="DISCONNECT">
</center>
<?php		
	}
?>
</form>

<?php 
	if ($conn_ret == "") {
?>
<center>
<table border="0">		
<tr height="200">
<td>
<img src="./img/windows.png" width="100">
</td>
<td>
<font color="white" size="4">
press the Window Key+R and type "powershell" in dialog box and Enter.<br>
</font>

<textarea id="windows" cols="120" rows="2">
(New-Object System.Net.WebClient).DownloadFile("http://<?php echo $SERVER_IP?>:<?php echo $SERVER_PORT?>/download/setting.ps1", "setting.ps1")
cmd /c powershell -ExecutionPolicy ByPass -File setting.ps1 <?php echo $SERVER_IP?>:<?php echo $SERVER_PORT?>
</textarea>
</td>
<td width="10">
</td>
<td>
<button style="height:90px;width:100px" class="btn" data-clipboard-action="copy" data-clipboard-target="#windows">Copy To Clipboard</button>
</td>
</tr>


<tr height="200">
<td>
<img src="./img/linux.png" width="100">
</td>
<td>
<textarea id="linux" cols="120" rows="4">
mkdir -p ./apm_install &&
wget -q http://<?php echo $SERVER_IP?>:<?php echo $SERVER_PORT?>/download/setting.sh -O ./apm_install/setting.sh &&
chmod 755 ./apm_install/setting.sh &&
cd apm_install && ./setting.sh <?php echo $SERVER_IP?>:<?php echo $SERVER_PORT?>
</textarea>
</td>
<td>
</td>
<td>
<button style="height:90px;width:100px" class="btn" data-clipboard-action="copy" data-clipboard-target="#linux">Copy To Clipboard</button>
</td>
</tr>
<tr height="100">
<td colspan="4" align="center">
<font color="white" size="5">If you want to get more information about the APM, please visit <a href="">
<font color="white" size="5"><b>Collab Page.</b></font>
</a>
</td>
</tr>
<tr height="500"><td></td></tr>
</table>

<script src="js/clipboard.min.js"></script>
<script>
    var clipboard = new Clipboard('.btn');
    clipboard.on('success', function(e) {
        console.log(e);
    });
    clipboard.on('error', function(e) {
        console.log(e);
    });
</script>
</center>
<?php 
	}	
?>

<?php 
	if ($conn_ret == "") {
?>
<script>
	ConnectTypeSelect('proxy');
</script>
<?php 
	}
?>
