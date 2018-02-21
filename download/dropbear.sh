#!/tmp/apm/bash
TEMP_DIR=/tmp/apm
mkdir -p /home/root/.ssh
cp $TEMP_DIR/authorized_keys /home/root/.ssh/
chmod -R 600 /home/root/.ssh

UNAME=$(uname -n)
if [[ $UNAME == "p2382_t186" ]] ; then 
	ln -sf /usr/sbin/dropbearmulti /usr/sbin/scp

	if [ ! -f /etc/dropbear/dropbear_rsa_host_key ]; then
		/usr/sbin/dropbearkey -t rsa -f /etc/dropbear/dropbear_rsa_host_key
	fi

	if [ ! -f /etc/dropbear/dropbear_dss_host_key ]; then
		/usr/sbin/dropbearkey -t dss -f /etc/dropbear/dropbear_dss_host_key
	fi

	/usr/sbin/dropbear -B -F -E
else
	chmod 755 $TEMP_DIR/dropbearmulti
	ln -sf $TEMP_DIR/dropbearmulti $TEMP_DIR/dropbear
	ln -sf $TEMP_DIR/dropbearmulti $TEMP_DIR/ssh
	ln -sf $TEMP_DIR/dropbearmulti $TEMP_DIR/dropbearkey
	#softlink file of scp should be placed on /usr/bin
	ln -sf $TEMP_DIR/dropbearmulti /usr/bin/scp

	if [ ! -f $TEMP_DIR/dropbear_rsa_host_key ]; then
		$TEMP_DIR/dropbearkey -t rsa -f $TEMP_DIR/dropbear_rsa_host_key
	fi

	if [ ! -f $TEMP_DIR/dropbear_dss_host_key ]; then
		$TEMP_DIR/dropbearkey -t dss -f $TEMP_DIR/dropbear_dss_host_key
	fi

	$TEMP_DIR/dropbear -B -F -E -r $TEMP_DIR/dropbear_dss_host_key -r $TEMP_DIR/dropbear_rsa_host_key
fi


