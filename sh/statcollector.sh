TEMP_DIR=/tmp/apm
#!$TEMP_DIR/bash
FILTER_STR="bash|sh|login|grep|procrank|vbtd|awk|dropbear|agetty|\["

function die {
    echo >&2 "$@"
    exit 1
}

INTERVAL=3
DEVICE_INSTALL_DIR="/tmp/apm/"
DEVICE_PRELOAD_CMD="LD_PRELOAD=${DEVICE_INSTALL_DIR}libc-2.24.so ${DEVICE_INSTALL_DIR}ld-2.24.so"
PS_CMD="${DEVICE_PRELOAD_CMD} ${DEVICE_INSTALL_DIR}ps"
PREV_CPU_TOTAL=0
PREV_CPU_USER=0
PREV_CPU_NICE=0
PREV_CPU_SYSTEM=0
PREV_CPU_IDLE=0
PREV_CPU_IOWAIT=0
PREV_CPU_IRQ=0
PREV_CPU_SOFTIRQ=0
PREV_PROC_CPU=0
declare -A PREV_PROC_CPU_ARY

while true; do 
	PS_EXE="${PS_CMD} -eo pid,cmd --sort -rss | grep -Ev \"${FILTER_STR}\" | awk '/^%s[0-9]|[0-9]/ {printf \"%d|%s|%s_%d\n\", \$1, \$2, \$2, \$1}'"
	#PS_EXE="${PS_CMD} -eo pid,cmd --sort -rss | grep kernel_logger | awk '/^%s[0-9]|[0-9]/ {printf \"%d|%s|%s_%d\n\", \$1, \$2, \$2, \$1}'"

	PROCARY=($(eval $PS_EXE))
	#declare -p PROCARY
	truncate -s 0 $TEMP_DIR/proc_realtime
	truncate -s 0 $TEMP_DIR/cpu_realtime

	#Global CPU Statistics 
	#1:total, 2:user, 3:nice, 4:system, 5:idle, 6:iowait, 7:irq, 8:softirq
	CURR_CPU=$(cat /proc/stat | awk '/^cpu\s/ {total=0; for (i=0;i<NF;i++) total=total+$i; print total " " $2 " " $3 " " $4 " " $5 " " $6 " " $7 " " $8}')
	read CURR_CPU_TOTAL CURR_CPU_USER CURR_CPU_NICE CURR_CPU_SYSTEM CURR_CPU_IDLE CURR_CPU_IOWAIT CURR_CPU_IRQ CURR_CPU_SOFTIRQ <<< "$(echo $CURR_CPU)"

	let "DIFF_CPU_TOTAL=$CURR_CPU_TOTAL-$PREV_CPU_TOTAL"
	let "DIFF_CPU_USER=$CURR_CPU_USER-$PREV_CPU_USER"
	let "DIFF_CPU_NICE=$CURR_CPU_NICE-$PREV_CPU_NICE"
	let "DIFF_CPU_SYSTEM=$CURR_CPU_SYSTEM-$PREV_CPU_SYSTEM"
	let "DIFF_CPU_IDLE=$CURR_CPU_IDLE-$PREV_CPU_IDLE"
	let "DIFF_CPU_IOWAIT=$CURR_CPU_IOWAIT-$PREV_CPU_IOWAIT"
	let "DIFF_CPU_IRQ=$CURR_CPU_IRQ-$PREV_CPU_IRQ"
	let "DIFF_CPU_SOFTIRQ=$CURR_CPU_SOFTIRQ-$PREV_CPU_SOFTIRQ"
	if [ $DIFF_CPU_TOTAL -lt 0 ]; then DIFF_CPU_TOTAL=0; fi
	if [ $DIFF_CPU_USER -lt 0 ]; then DIFF_CPU_USER=0; fi
	if [ $DIFF_CPU_NICE -lt 0 ]; then DIFF_CPU_NICE=0; fi
	if [ $DIFF_CPU_SYSTEM -lt 0 ]; then DIFF_CPU_SYSTEM=0; fi
	if [ $DIFF_CPU_IDLE -lt 0 ]; then DIFF_CPU_IDLE=0; fi
	if [ $DIFF_CPU_IOWAIT -lt 0 ]; then DIFF_CPU_IOWAIT=0; fi
	if [ $DIFF_CPU_IRQ -lt 0 ]; then DIFF_CPU_IRQ=0; fi
	if [ $DIFF_CPU_SOFTIRQ -lt 0 ]; then DIFF_CPU_SOFTIRQ=0; fi
	#1:User, 2:Nice, 3:System, 4:Idle, 5:IoWait, 6:Hardware irq, 7:Software irq >>
	#1:Idle, 2:User, 3:System, 4:Nice, 5:IoWait, 6:Hardware irq, 7:Software irq
	echo "${DIFF_CPU_TOTAL} ${DIFF_CPU_USER} ${DIFF_CPU_NICE} ${DIFF_CPU_SYSTEM} ${DIFF_CPU_IDLE} ${DIFF_CPU_IOWAIT} ${DIFF_CPU_IRQ} ${DIFF_CPU_SOFTIRQ}" | awk '{printf "%d %d %d %d %d %d %d\n", $5/$1*100, $2/$1*100, $4/$1*100, $3/$1*100, $6/$1*100, $7/$1*100, $8/$1*100}' > $TEMP_DIR/cpu_realtime
	PREV_CPU_TOTAL="$CURR_CPU_TOTAL"
	PREV_CPU_USER="$CURR_CPU_USER"
	PREV_CPU_NICE="$CURR_CPU_NICE"
	PREV_CPU_SYSTEM="$CURR_CPU_SYSTEM"
	PREV_CPU_IDLE="$CURR_CPU_IDLE"
	PREV_CPU_IOWAIT="$CURR_CPU_IOWAIT"
	PREV_CPU_IRQ="$CURR_CPU_IRQ"
	PREV_CPU_SOFTIRQ="$CURR_CPU_SOFTIRQ"

	for (( i = 0; i < ${#PROCARY[@]}; i++ ))
	do
		#read -a ARY <<< "$(echo ${PROCARY[i]} | sed 's/|/& /g')"
		#IFS="|" read -ra ARY <<< "$(echo ${PROCARY[i]})"
		IFS="|" read ARY_PID ARY_CMD ARY_CMDPID <<< "$(echo ${PROCARY[i]})"
		#declare -p PROCARY
		#echo $ARY_CMDPID 
		if [ ! -d /proc/${ARY_PID} ]; then
			continue
		fi
		PROC_PSS=$(awk 'BEGIN {i=0} /^Pss/ {i = i + $2} END {print i}' /proc/$ARY_PID/smaps)
		#minflt(10), majflt(12), utime(14), stime(15), #thread(20)
		PROC_STAT=$(cat /proc/$ARY_PID/stat | awk '{print $14+$15}')
		read CURR_PROC_CPU <<< "$(echo ${PROC_STAT})"
		
		PREV_PROC_CPU=${PREV_PROC_CPU_ARY[${ARY_CMDPID}]}
		if [ -z "${PREV_PROC_CPU}" ]; then PREV_PROC_CPU=0; fi
		let "DIFF_PROC_CPU=$CURR_PROC_CPU-$PREV_PROC_CPU"
		if [ $DIFF_PROC_CPU -lt 0 ]; then DIFF_PROC_CPU=0; fi
		let "CPU_PERC=(1000*$DIFF_PROC_CPU/$DIFF_CPU_TOTAL/10)"
		PREV_PROC_CPU_ARY[${ARY_CMDPID}]=$CURR_PROC_CPU

		echo "$ARY_PID $ARY_CMD $PROC_PSS $CPU_PERC" >> $TEMP_DIR/proc_realtime
		#echo "$ARY_PID $ARY_CMD $PROC_PSS $DIFF_CPU_TOTAL $DIFF_PROC_CPU $CPU_PERC"
	done
	cp $TEMP_DIR/proc_realtime $TEMP_DIR/proc_cached
	cp $TEMP_DIR/cpu_realtime $TEMP_DIR/cpu_cached
	sleep $INTERVAL
done
