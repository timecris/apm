Write-Host "Checking if adb binary exist or not"
cmd /c adb version
$return_code=((echo $?))
if ($return_code) {
	$adbbin="adb"
} else {
	a version
	$return_code=((echo $?))
	if ($return_code) {
		$adbbin="a"
	} else {
		Write-Host "adb binary is not found. install adb or check permission to execute adb binary"
		exit
	}
}
Write-Host "adb binary name is $adbbin"

$temp_dir="/tmp/apm"
$hostaddr=$args[0]

if (!$hostaddr) {
	exit
}

$rinetd_pid=((Get-Process | findstr rinetd))
if ($rinetd_pid) {
	Write-Host "ERROR: rinetd daemon is running already. please stop this process first."
	exit
}

Write-Host "Kill adb server"
cmd /c $adbbin kill-server
Write-Host "Start adb server"
cmd /c $adbbin start-server


$adb_devices=((cmd /c $adbbin devices))
$dev_device=((echo $adb_devices | Select -Index 1))

if (!$dev_device) {
	Write-Host "no device. device is not connecting through USB".
	exit
}

Write-Host "Downloading from $hostaddr"
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/download/rinetd.eee", "rinetd.eee")
Move-Item rinetd.eee rinetd.exe -Force
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/download/rinetd.ini", "rinetd.ini")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/download/dropbearmulti", "dropbearmulti")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/key/authorized_keys", "authorized_keys")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/download/dropbear.sh", "dropbear.sh")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/download/dropbear.service", "dropbear.service")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/measure.sh", "measure.sh")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/parser.sh", "parser.sh")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/realtime_global.sh", "realtime_global.sh")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/realtime_proc.sh", "realtime_proc.sh")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/realtime_process.sh", "realtime_process.sh")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/realtime_vmstat.sh", "realtime_vmstat.sh")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/memtrim.sh", "memtrim.sh")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/hmiapp_memtrim.sh", "hmiapp_memtrim.sh")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/statcollector.sh", "statcollector.sh")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/download/statcollector.service", "statcollector.service")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/webapp_list", "webapp_list")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/timeout", "timeout")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/ps", "ps")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/procrank", "procrank")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/bash", "bash")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/bc", "bc")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/pmap", "pmap")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/find", "find")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/libc-2.24.so", "libc-2.24.so")
(New-Object System.Net.WebClient).DownloadFile("http://$hostaddr/sh/ld-2.24.so", "ld-2.24.so")

Write-Host "Push necessary binaries to device"
cmd /c $adbbin shell "mkdir -p $temp_dir"
cmd /c $adbbin push dropbearmulti $temp_dir
cmd /c $adbbin push dropbear.sh $temp_dir
cmd /c $adbbin push dropbear.service $temp_dir
cmd /c $adbbin push authorized_keys $temp_dir
cmd /c $adbbin push measure.sh $temp_dir
cmd /c $adbbin push parser.sh $temp_dir
cmd /c $adbbin push realtime_global.sh $temp_dir
cmd /c $adbbin push realtime_proc.sh $temp_dir
cmd /c $adbbin push realtime_process.sh $temp_dir
cmd /c $adbbin push realtime_vmstat.sh $temp_dir
cmd /c $adbbin push memtrim.sh $temp_dir
cmd /c $adbbin push hmiapp_memtrim.sh $temp_dir
cmd /c $adbbin push statcollector.sh $temp_dir
cmd /c $adbbin push statcollector.service $temp_dir
cmd /c $adbbin push webapp_list $temp_dir
cmd /c $adbbin push timeout $temp_dir
cmd /c $adbbin push ps $temp_dir
cmd /c $adbbin push procrank $temp_dir
cmd /c $adbbin push bash $temp_dir
cmd /c $adbbin push bc $temp_dir
cmd /c $adbbin push pmap $temp_dir
cmd /c $adbbin push find $temp_dir
cmd /c $adbbin push libc-2.24.so $temp_dir
cmd /c $adbbin push ld-2.24.so $temp_dir

Write-Host "Create Account for monitoring"
cmd /c $adbbin shell "adduser apm --disabled-password -s /bin/sh"
cmd /c $adbbin shell "passwd -d apm"

Write-Host "Remount partition for installing ssh server"
cmd /c $adbbin shell "mount -o rw,remount /"
cmd /c $adbbin push dropbear.service /etc/systemd/system/dropbear.service
cmd /c $adbbin statcollector.service /etc/systemd/system/statcollector.service
cmd /c $adbbin shell "chmod 755 $temp_dir/*"
Write-Host "Install SSH server(dropbear) to systemd"
cmd /c $adbbin shell "systemctl disable dropbear"
cmd /c $adbbin shell "systemctl enable dropbear"
Write-Host "Start SSH server(dropbear)"
cmd /c $adbbin shell "systemctl start dropbear"

echo "Install statcollector to systemd"
cmd /c $adbbin "systemctl disable statcollector"
cmd /c $adbbin "systemctl enable statcollector"
echo "Start statcollector service"
cmd /c $adbbin "systemctl start statcollector"

Write-Host "Forwarding tcp:8022 to tcp:22"
cmd /c $adbbin forward --remove-all
cmd /c $adbbin forward tcp:8022 tcp:22

$adbport=((netstat -ano | findstr ":8022"))
if (!$adbport) {
	Write-Host "ERROR: ADB forwading port is not listening."
	exit
}

Write-Host "Forwarding tcp:8021 to tcp:8022"
cmd /c rinetd.exe -c rinetd.ini
