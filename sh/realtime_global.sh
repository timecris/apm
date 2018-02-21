TEMP_DIR=/tmp/apm
#!$TEMP_DIR/bash
ITEM=($(cat /proc/meminfo | awk '{gsub(":", ""); print $1}'))
VALUE=($(cat /proc/meminfo | awk '{printf "%.2f ", $2/1024}'))

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
#1:User, 2:Nice, 3:System, 4:Idle, 5:IoWait, 6:Hardware irq, 7:Software irq
printf "|7,Idle,User,System,Nice,IOwait,IRQ,SoftIRQ,"
cat $TEMP_DIR/cpu_cached | sed 's/ /,/g'

printf "|"
VMITEM=($(cat /proc/vmstat | awk '{print $1}'))
VMVALUE=($(cat /proc/vmstat | awk '{print $2}'))

#Count of VMITEM array
printf ${#VMITEM[@]}
printf ","

#Name of VMITEM array
for (( i = 0; i < ${#VMITEM[@]}; i++ ))
do
	printf ${VMITEM[$i]}
	printf ","
done

for (( i = 0; i < ${#VMVALUE[@]}; i++ ))
do
	printf ${VMVALUE[$i]}
	printf ","
done
