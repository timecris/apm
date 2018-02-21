TEMP_DIR=/tmp/apm
#!$TEMP_DIR/bash
ITEM=($(cat /proc/vmstat | awk '{print $1}'))
VALUE=($(cat /proc/vmstat | awk '{print $2}'))

#Count of ITEM array
printf ${#ITEM[@]}
printf ","

#Name of ITEM array
for (( i = 0; i < ${#ITEM[@]}; i++ ))
do
	printf ${ITEM[$i]}
	printf ","
done

for (( i = 0; i < ${#VALUE[@]}; i++ ))
do
	printf ${VALUE[$i]}
	printf ","
done

