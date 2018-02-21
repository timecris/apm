TEMP_DIR=/tmp/apm
#!$TEMP_DIR/bash
FILTER_TYPE=$1
FILTER_STR=$2
[ -z "${FILTER_TYPE}" ] && FILTER_TYPE="-Ev"
[ -z "${FILTER_STR}" ] && FILTER_STR="bash|sh|login|grep|procrank|vbtd|awk|dropbear|agetty"

#$TEMP_DIR/procrank | grep -Ev "bash|sh|login|grep|procrank|vbtd|awk|dropbear|agetty|IDVerifierPluginHMI|IdentityManager|micommanager|dlt-cdh|cedmd" | awk 'BEGIN { cnt=0; } /K+\s*[0-9]*K+\s*[0-9]*K+\s*[0-9]*/ {cnt++; item=(item)","($6"_"$1); gsub("K", "", $4); val=(val)","($4)} END { printf "%d%s%s", cnt,item,val }'

#MEMORY
cat $TEMP_DIR/proc_cached | grep "${FILTER_TYPE}" "${FILTER_STR}" | sort -r -n -k3 | awk 'BEGIN { cnt=0; } {cnt++; item=(item)","($2"_"$1); val=(val)","($3)} END { printf "%d%s%s", cnt,item,val }'
printf "|"
#CPU USAGE
cat $TEMP_DIR/proc_cached | grep "${FILTER_TYPE}" "${FILTER_STR}" | sort -r -n -k4 | awk 'BEGIN { cnt=0; } {cnt++; item=(item)","($2"_"$1); val=(val)","($4)} END { printf "%d%s%s", cnt,item,val }'
