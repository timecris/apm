TEMP_DIR=/tmp/apm
#!$TEMP_DIR/bash

function die {
    echo >&2 "$@"
    exit 1
}

[ -z "$1" ] && die "Please specify name of process. ex) memtrim.sh homeapp"

PIDARY=($(pgrep $1))

#Count of ITEM array
if [[ ${#PIDARY[@]} == 0 ]]; then
	echo "No such process"
elif [[ ${#PIDARY[@]} > 1 ]]; then
	echo "there are more than two processes"
fi

#Name of ITEM array
for (( i = 0; i < ${#PIDARY[@]}; i++ ))
do
	PID=${PIDARY[i]}
	echo PID:$PID
	
	BEFORE=($(cat /proc/$PID/smaps | awk '/Size\:/ {size+=$2} /Rss\:/ {rss+=$2} /Pss\:/ {pss+=$2} /Anonymous\:/ {anon+=$2} /Swap\:/ {swap+=$2} END {print size " " rss " " pss " " anon " " swap}'))

	kill -n 59 $PID
	sleep 1
	kill -n 58 $PID
	sleep 1
	kill -n 59 $PID
	sleep 1

	AFTER=($(cat /proc/$PID/smaps | awk '/Size\:/ {size+=$2} /Rss\:/ {rss+=$2} /Pss\:/ {pss+=$2} /Anonymous\:/ {anon+=$2} /Swap\:/ {swap+=$2} END {print size " " rss " " pss " " anon " " swap}'))

	printf '%-10s %-10s %-10s %-10s %-10s %-10s\n' " " SIZE\(K\) RSS\(K\) PSS\(K\) ANON\(K\) SWAP\(K\)
	printf '%-10s %-10s %-10s %-10s %-10s %-10s\n' BEFORE ${BEFORE[0]} ${BEFORE[1]} ${BEFORE[2]} ${BEFORE[3]} ${BEFORE[4]}
	printf '%-10s %-10s %-10s %-10s %-10s %-10s\n' AFTER ${AFTER[0]} ${AFTER[1]} ${AFTER[2]} ${AFTER[3]} ${AFTER[4]}
	printf '%-10s %-10s %-10s %-10s %-10s %-10s\n' DIFF $(( ${AFTER[0]}-${BEFORE[0]} )) $(( ${AFTER[1]}-${BEFORE[1]} )) $(( ${AFTER[2]}-${BEFORE[2]} )) $(( ${AFTER[3]}-${BEFORE[3]} )) $(( ${AFTER[4]}-${BEFORE[4]} ))
done
