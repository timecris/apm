TEMP_DIR=/tmp/apm
#!$TEMP_DIR/bash


function die {
    echo >&2 "$@"
    exit 1
}

DEVICE_INSTALL_DIR="/tmp/apm/"
DEVICE_PRELOAD_CMD="LD_PRELOAD=${DEVICE_INSTALL_DIR}libc-2.24.so ${DEVICE_INSTALL_DIR}ld-2.24.so"
PS_CMD="${DEVICE_PRELOAD_CMD} ${DEVICE_INSTALL_DIR}ps"
PS_EXE="${PS_CMD} -eo pid,cmd --sort -rss | grep -v grep | grep /app/hmiapp | awk '{printf \"%d|%s\n\", \$1, \$2}'"
PROCARY=($(eval $PS_EXE))

TOTAL_DIFF_SIZE=0
TOTAL_DIFF_RSS=0
TOTAL_DIFF_PSS=0
TOTAL_DIFF_ANON=0
TOTAL_DIFF_SWAP=0

for (( i = 0; i < ${#PROCARY[@]}; i++ ))
do
	IFS="|" read -ra ARYYY <<< "$(echo ${PROCARY[i]})"
	#declare -p ARYYY
	ARY_PID=${ARYYY[0]}
	ARY_DISPLAY=${ARYYY[1]}
	BEFORE=($(cat /proc/${ARY_PID}/smaps | awk '/Size\:/ {size+=$2} /Rss\:/ {rss+=$2} /Pss\:/ {pss+=$2} /Anonymous\:/ {anon+=$2} /Swap\:/ {swap+=$2} END {print size " " rss " " pss " " anon " " swap}'))
	kill -n 59 ${ARY_PID}
	kill -n 58 ${ARY_PID}
	kill -n 59 ${ARY_PID}
	AFTER=($(cat /proc/${ARY_PID}/smaps | awk '/Size\:/ {size+=$2} /Rss\:/ {rss+=$2} /Pss\:/ {pss+=$2} /Anonymous\:/ {anon+=$2} /Swap\:/ {swap+=$2} END {print size " " rss " " pss " " anon " " swap}'))
	let TOTAL_DIFF_SIZE+=$(( ${AFTER[0]}-${BEFORE[0]} ))
	let TOTAL_DIFF_RSS+=$(( ${AFTER[1]}-${BEFORE[1]} ))
	let TOTAL_DIFF_PSS+=$(( ${AFTER[2]}-${BEFORE[2]} ))
	let TOTAL_DIFF_ANON+=$(( ${AFTER[3]}-${BEFORE[3]} ))
	let TOTAL_DIFF_SWAP+=$(( ${TOTAL_DIFF_SWAP}+${AFTER[4]}-${BEFORE[4]} ))

#	printf '%-10s %-10s %-10s %-10s %-10s %-10s\n' " " SIZE\(K\) RSS\(K\) PSS\(K\) ANON\(K\) SWAP\(K\)
#	printf '%-10s %-10s %-10s %-10s %-10s %-10s\n' BEFORE ${BEFORE[0]} ${BEFORE[1]} ${BEFORE[2]} ${BEFORE[3]} ${BEFORE[4]}
#	printf '%-10s %-10s %-10s %-10s %-10s %-10s\n' AFTER ${AFTER[0]} ${AFTER[1]} ${AFTER[2]} ${AFTER[3]} ${AFTER[4]}
#	printf '%-10s %-10s %-10s %-10s %-10s %-10s\n' DIFF $(( ${AFTER[0]}-${BEFORE[0]} )) $(( ${AFTER[1]}-${BEFORE[1]} )) $(( ${AFTER[2]}-${BEFORE[2]} )) $(( ${AFTER[3]}-${BEFORE[3]} )) $(( ${AFTER[4]}-${BEFORE[4]} ))
done

echo ""
echo "Result of memtrimming all hmiapps"
echo "SIZE ${TOTAL_DIFF_SIZE}"
echo "RSS  ${TOTAL_DIFF_RSS}"
echo "PSS ${TOTAL_DIFF_PSS}"
echo "ANON ${TOTAL_DIFF_ANON}"
echo "SWAP ${TOTAL_DIFF_SWAP}"


