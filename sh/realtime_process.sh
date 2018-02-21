TEMP_DIR=/tmp/apm
#!$TEMP_DIR/bash
PROCESS_NAME=$1
[ -z "$PROCESS_NAME" ] && echo "1,NO SUCH PROCESS,0" && exit

LD_PRELOAD=$TEMP_DIR/libc-2.24.so $TEMP_DIR/ld-2.24.so $TEMP_DIR/pmap -X $(pidof $PROCESS_NAME) | awk 'END {printf "10,Size,Rss,Pss,Referenced,Anonymous,Shared_Hugetlb,Private_Hugetlb,Swap,SwapPss,Locked,%d,%d,%d,%d,%d,%d,%d,%d,%d,%d", $1, $2, $3, $4, $5, $6, $7, $8, $9, $10}'
