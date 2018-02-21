temp_dir="/tmp/apm"
hostaddr=$1


function warn {
	if ! eval "$@"; then
		echo >&2 "WARNING: command failed \"$@\""
	fi
}

function die {
	echo >&2 "$@"
	exit 1
}

[ -z "$hostaddr" ] && exit

adb forward --remove-all
rinetd_pid=$(pgrep rinetd)
[ ! -z "$rinetd_pid" ] && echo "ERROR: rinetd daemon is running already. please stop this process first.  ex)sudo killall rinetd" &&  exit 1

echo "Kill adb server"
adb kill-server
echo "Start adb server"
adb start-server

dev_check=$(adb devices | sed -n '/List of devices attached/{n;p;}')
[ -z "$dev_check" ] && 	echo "no device. device is not connecting through USB". && exit 1

echo "Downloading from $hostaddr"
wget -q http://$hostaddr/download/rinetd -O rinetd
wget -q http://$hostaddr/download/rinetd.ini -O rinetd.ini
wget -q http://$hostaddr/download/dropbearmulti -O dropbearmulti
wget -q http://$hostaddr/key/authorized_keys -O authorized_keys
wget -q http://$hostaddr/download/dropbear.sh -O dropbear.sh
wget -q http://$hostaddr/download/dropbear.service -O dropbear.service
wget -q http://$hostaddr/sh/measure.sh -O measure.sh
wget -q http://$hostaddr/sh/parser.sh -O parser.sh
wget -q http://$hostaddr/sh/realtime_global.sh -O realtime_global.sh
wget -q http://$hostaddr/sh/realtime_proc.sh -O realtime_proc.sh
wget -q http://$hostaddr/sh/realtime_process.sh -O realtime_process.sh
wget -q http://$hostaddr/sh/realtime_vmstat.sh -O realtime_vmstat.sh
wget -q http://$hostaddr/sh/memtrim.sh -O memtrim.sh
wget -q http://$hostaddr/sh/hmiapp_memtrim.sh -O hmiapp_memtrim.sh
wget -q http://$hostaddr/sh/statcollector.sh -O statcollector.sh
wget -q http://$hostaddr/download/statcollector.service -O statcollector.service
wget -q http://$hostaddr/sh/webapp_list -O webapp_list
wget -q http://$hostaddr/sh/timeout -O timeout
wget -q http://$hostaddr/sh/ps -O ps
wget -q http://$hostaddr/sh/procrank -O procrank
wget -q http://$hostaddr/sh/bash -O bash
wget -q http://$hostaddr/sh/bc -O bc
wget -q http://$hostaddr/sh/pmap -O pmap
wget -q http://$hostaddr/sh/find -O find
wget -q http://$hostaddr/sh/libc-2.24.so -O libc-2.24.so
wget -q http://$hostaddr/sh/ld-2.24.so -O ld-2.24.so

echo "Push necessary binaries to device"
adb shell "mkdir -p $temp_dir"
adb push dropbearmulti $temp_dir
adb push dropbear.sh $temp_dir
adb push dropbear.service $temp_dir
adb push authorized_keys $temp_dir
adb push measure.sh $temp_dir
adb push parser.sh $temp_dir
adb push realtime_global.sh $temp_dir
adb push realtime_proc.sh $temp_dir
adb push realtime_process.sh $temp_dir
adb push realtime_vmstat.sh $temp_dir
adb push memtrim.sh $temp_dir
adb push hmiapp_memtrim.sh $temp_dir
adb push statcollector.sh $temp_dir
adb push statcollector.service $temp_dir
adb push webapp_list $temp_dir
adb push timeout $temp_dir
adb push ps $temp_dir
adb push procrank $temp_dir
adb push bash $temp_dir
adb push bc $temp_dir
adb push pmap $temp_dir
adb push find $temp_dir
adb push libc-2.24.so $temp_dir
adb push ld-2.24.so $temp_dir

echo "Create Account for monitoring"
#adb shell adduser apm --disabled-password -s /bin/sh
adb shell passwd -d root

echo "Remount partition for installing ssh server"
adb shell "mount -o rw,remount /"
adb push dropbear.service /etc/systemd/system/dropbear.service
adb push statcollector.service /etc/systemd/system/statcollector.service

adb shell "chmod 755 $temp_dir/*"
echo "Install SSH server(dropbear) to systemd"
adb shell "systemctl disable dropbear"
adb shell "systemctl enable dropbear"
echo "Start SSH server(dropbear)"
adb shell "systemctl start dropbear"

echo "Install statcollector to systemd"
adb shell "systemctl disable statcollector"
adb shell "systemctl enable statcollector"
echo "Start statcollector service"
adb shell "systemctl start statcollector"

echo "Forwarding tcp:8022 to tcp:22"
adb forward --remove-all
adb forward tcp:8022 tcp:22

port_check=$(netstat -ano | grep ":8022")
[ -z "$port_check" ] && echo "ADB forwarding port is not listening." && exit 1

echo "Forwarding tcp:8021 to tcp:8022"
chmod 755 rinetd
./rinetd -f -c ./rinetd.ini
