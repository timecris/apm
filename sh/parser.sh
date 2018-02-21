#!/tmp/bash

RESULT_MEMINFO="resultmeminfo"
RESULT_PSS="resultpss"
RESULT_RSS="resultrss"
RESULT_USS="resultuss"
RESULT_LAUNCHTIME="resultlaunchtime"
PAGE_OUTPUT="resultpage"
CPU_OUTPUT="resultcpuusage"
CPU_VARIATION_OUTPUT="resultcpuvariationusage"
STAT_OUTPUT="resultstat"
DISKSTAT_OUTPUT="resultdiskstat"
IONHEAP_OUTPUT="resultionheap"

MEMINFO_ITEM=(AFM $(cat /proc/meminfo | awk '{print $1}'))
PAGE_ITEM=($(cat /proc/vmstat | grep pg | grep -v unevictable | awk '{print $1}'))

#non-nice user cpu ticks, nice user cpu ticks, system cpu ticks, idle cpu ticks, IO-wait cpu ticks, IRQ cpu ticks, softirq cpu ticks
CPU_ITEM=(USER NICEUSER SYSTEM IDLE IOWAIT IRQ SOFTIRQ)

STAT_ITEM=(ctxt btime processes)
DISKSTAT_ITEM=(R_TOTAL R_MERGED R_SECTORS R_MS W_TOTAL W_MERGED W_SECTORS W_MS CUR SEC)
IONHEAP_ITEM=("   total" "total orphaned")

SS_ITEM_NAME=(
"QtWebProcess"
"LSM"
"MaliitServer"
"WebAppMgr"
"SYSTEMSERVICE"
"MUSIC"
"ACCOUNTS"
"QVOICE"
"LGHEALTHW"
"LINKER"
"CONTACT"
"PHOTOS"
)

SS_ITEM=(
"K  /usr/bin/QtWebProcess"
"K  /usr/sbin/MaliitServer"
"K  /usr/bin/WebAppMgr"
)

LAUNCHTIME_ITEM=(LAUNCHTIME FOCUSTIME)
PROCESS_ITEM=(QtWebProcess LSM WebAppMgr MaliitServer SYSTEMSERVICE)

RESULT=()
FILELIST=()
OUTPUT="/tmp/result"

ts_get_msec()
{
        ts=$1
        HH=($(echo $ts | cut -c 12-13 | sed -e 's/^0//g'))
        MM=($(echo $ts | cut -c 15-16 | sed -e 's/^0//g'))
        SS=($(echo $ts | cut -c 18-19 | sed -e 's/^0//g'))
        MS=($(echo $ts | cut -c 21-22 | sed -e 's/^0//g'))

        echo $(((HH*60*60*1000)+(MM*60*1000)+(SS*1000)+MS))
}

function GetResultList()
{
        RESULT=($(ls -al /tmp/result | awk '{print $9}' | grep -v "\."))

        for (( i = 0; i < ${#RESULT[@]}; i++ ))
        do
                name=${RESULT[$i]}
		echo $name
        done
}

function init()
{
        mount -o rw,remount /
        GetResultList
}

function Parse_meminfo()
{
	printf "Analyzing meminfo.. CWD : "
	pwd
                
	Print_filename $RESULT_MEMINFO
	printf "AFM" >> resultmeminfo
	find . -name "[0-9][0-9]*" | sort | xargs sed -n '/USABLE/{n;p;}' | awk '{$2=$1*4/1024; printf ",%.2f", $2}' >> $RESULT_MEMINFO

	for (( j = 1; j < ${#MEMINFO_ITEM[@]}; j++ ))
	do
		printf "\n" >> $RESULT_MEMINFO
		printf ${MEMINFO_ITEM[$j]} >> $RESULT_MEMINFO

		cmd="find . -name \"[0-9][0-9]*\" | sort | xargs grep  \"^${MEMINFO_ITEM[$j]}\" | awk '{\$MB=\$2/1024; printf \",%.2f\", \$MB}' >> $RESULT_MEMINFO"
		eval $cmd
	done
}


function Parse_ss()
{
        IFS=$'?'
	printf "Analyzing $1 data.. CWD : "
	pwd

	Print_filename $2

	#4-RSS, 5-PSS, 6-USS
	if [ "$1" = "RSS" ] ; then
		column=4
		t_column=0
	elif [ "$1" = "PSS" ] ; then
		column=5
		t_column=2
	elif [ "$1" = "USS" ] ; then
		column=6
		t_column=3
	fi

        for (( j = 0; j < ${#SS_ITEM[@]}; j++ ))
        do
	        printf "${SS_ITEM_NAME[$j]}" >> $2

                #store matched files
                cmd="find . -name \"[0-9][0-9]*\" | sort | xargs grep -L \""${SS_ITEM[$j]}"\" > tmp_match"
                eval $cmd
                #store unmatched files
                cmd="find . -name \"[0-9][0-9]*\" | sort | xargs grep -H \""${SS_ITEM[$j]}"\" > tmp_not_match"
                eval $cmd
                #merge two result above
                cmd="cat tmp_match tmp_not_match | sort | awk -v cv=$column '{\$MB=\$cv/1024; printf \",%.2f\", \$MB}' >> $2"
                eval $cmd

                printf "\n" >> $2
        done

        printf "TOTAL_$1" >> $2
        find . -name "[0-9][0-9]*" | sort | xargs grep  "TOTAL" | awk -v cv=$t_column '{$MB=$cv/1024; printf ",%.2f", $MB}' >> $2
        unset IFS

        rm tmp_match
        rm tmp_not_match
}

function Parse_process()
{
	printf "Analyzing Process Map data.. CWD : "
        pwd
        #files in test directory
        FILES=($(find * -type f -name "[0-9][0-9]*" | sort))

        #Analyze pmap result per PROCESS_ITEM
        for (( q = 0; q < ${#PROCESS_ITEM[@]}; q++ ))
        do
	        for (( j = 0; j < ${#FILES[@]}; j++ ))
                do
	                cmd="sed -n -e '/${PROCESS_ITEM[$q]}_BEGIN/,/${PROCESS_ITEM[$q]}_END/ p' ${FILES[$j]} | sed -e '1,3d' | sed '$d' | sed '$d' \
                             | sed '$d' | sed 's/\[//g' | sed 's/\]//g' | awk '{if(\$13==\"\") \$13=\"anon\"; printf \"%s,%d\n\", \$13, \$8; }' > tmp"
                        #echo $cmd
                        eval $cmd
			seq=($(printf "%02d" $j))
                        cmd="awk -F, '{a[\$1]+=\$2;} END {for (i in a) print i \",\" a[i]}' tmp > tmp_${PROCESS_ITEM[$q]}_$seq"
                        #echo $cmd
                        eval $cmd
                done
        done


        #tmp files in test directory
        for (( q = 0; q < ${#PROCESS_ITEM[@]}; q++ ))
        do
	        PROCESS_SECTION=($(cat tmp_${PROCESS_ITEM[$q]}_00 | sort -n -t "," -k 2 -r | awk -F, '{print $1}'))
                Print_filename result${PROCESS_ITEM[$q]}

                for (( k = 0; k < ${#PROCESS_SECTION[@]}; k++ ))
                do
	                #split file name by ".so"
                        cmd="printf \"${PROCESS_SECTION[$k]}\" | awk -F'.so' '{printf \"%s\", \$1}' >> result${PROCESS_ITEM[$q]}"
                        eval "$cmd"
                        cmd="find * -type f -name \"tmp_${PROCESS_ITEM[$q]}_[0-9][0-9]*\" | sort | xargs grep \"^"${PROCESS_SECTION[$k]}",\" | awk -F, '{printf \",%d\", \$2}' >> result${PROCESS_ITEM[$q]}"
                        #echo "$cmd"
                        eval "$cmd"
                        printf "\n" >> result${PROCESS_ITEM[$q]}
                done
        done
}

function Print_appname()
{
        #listing the files without "0_idle"
        printf "JSH" > $1
        APPNAME=($(find * -type f -name "[0-9][0-9]*" | sort | awk -F "_" '{print $3}' | grep -v "idle" | awk '{split($1,a,"."); {printf ",%s", a[4]}}' | tr [:lower:] [:upper:] ))
        echo $APPNAME >> $1
}

function Print_filename()
{
        #listing the files with "0_idle"
        printf "JSH" > $1
        APPNAME=($(find * -type f -name "[0-9][0-9]*" | sort | awk -F "_" '{print $3}' | awk '{split($1,a,"."); if (a[2]=="") { printf ",%s",a[1]} else {printf ",%s",a[4] }}' | tr [:lower:] [:upper:]))
        echo $APPNAME >> $1
}

function Parse_launchtime()
{
	printf "Analyzing App Launch time data.. CWD : "
        pwd

        Print_appname $RESULT_LAUNCHTIME

        #files in test directory
        FILES=($(find * -type f -name "[0-9][0-9]_L_*" | sort))

        printf "LAUNCHTIME" >> $RESULT_LAUNCHTIME

        for (( j = 0; j < ${#FILES[@]}; j++ ))
        do
	        START=$(grep "LAUNCHTIME_" ${FILES[$j]} | awk -F "_" '{print $2}')
                END=$(grep "FOCUSTIME_" ${FILES[$j]} | awk -F "_" '{print $2}')

                if [ "$START" = "" ] ; then
	                START="0"
                        END="0"
                fi

                if [ "$END" = "" ] ; then
	                END="0"
                        START="0"
                fi

                M_START=$(ts_get_msec $START)
                M_END=$(ts_get_msec $END)
                DIFF=$(($M_END-$M_START))
                printf ","$DIFF >> $RESULT_LAUNCHTIME
        done
}

function Parse_page()
{
        printf "Analyzing Pagecache data.. CWD : "
        pwd

        Print_filename $PAGE_OUTPUT

        for (( i = 0; i < ${#PAGE_ITEM[@]}; i++ ))
        do
                find . -name "[0-9][0-9]*" | sort | xargs sed -n -e '/^VMSTAT_BEGIN/,/^VMSTAT_END/ p' | grep "${PAGE_ITEM[$i]}" |
                awk -v iv="$(($i+1))" -v last="${#PAGE_ITEM[@]}" '
                {
                        if (NR==1) {
                                printf "%s", $1
                        }
                        printf ",%d", $2
                }
                END {
                        if (iv!=last)
                                printf "\n"
                }' >> $PAGE_OUTPUT
        done
}

function Parse_cpuusage()
{
        printf "Analyzing CPU usage data.. CWD : "
        pwd

        Print_filename $CPU_OUTPUT

        for (( i = 0; i < ${#CPU_ITEM[@]}; i++ ))
        do
                find . -name "[0-9][0-9]*" | sort | xargs sed -n -e '/^STAT_BEGIN/,/^STAT_END/ p' | grep "cpu  " |
                awk -F " " -v cv="$(($i+2))" -v item="${CPU_ITEM[$i]}" -v iv="$(($i+1))" -v last="${#CPU_ITEM[@]}" '
                {
                        if (NR==1) {
                                printf "%s", item
                        }
                        printf ",%d", $cv
                }
                END {
                        if (iv!=last)
                                printf "\n"
                }' >> $CPU_OUTPUT
	done


        Print_appname $CPU_VARIATION_OUTPUT

        for (( i = 0; i < ${#CPU_ITEM[@]}; i++ ))
        do
                find . -name "[0-9][0-9]*" | sort | xargs sed -n -e '/^STAT_BEGIN/,/^STAT_END/ p' | grep "cpu  " |
                awk -F " " -v cv="$(($i+2))" -v item="${CPU_ITEM[$i]}" -v iv="$(($i+1))" -v last="${#CPU_ITEM[@]}" '
                {
                        if (NR==1) {
                                printf "%s", item
				base=$cv
                        } else {
                        	printf ",%d", $cv-base
				base=$cv
			}
                }
                END {
                        if (iv!=last)
                                printf "\n"
                }' >> $CPU_VARIATION_OUTPUT
	done
}

function Parse_stat()
{
        printf "Analyzing Stat data.. CWD : "
        pwd

        Print_filename $STAT_OUTPUT

        for (( i = 0; i < ${#STAT_ITEM[@]}; i++ ))
        do
                find . -name "[0-9][0-9]*" | sort | xargs sed -n -e '/^STAT_BEGIN/,/^STAT_END/ p' | grep "${STAT_ITEM[$i]}" |
                awk -v item="${STAT_ITEM[$i]}" -v iv="$(($i+1))" -v last="${#STAT_ITEM[@]}" '
                {
                        if (NR==1) {
                                printf "%s", item
                        }
                        printf ",%d", $2
                }
                END {
                        if (iv!=last)
                                printf "\n"
                }' >> $STAT_OUTPUT
        done
}

function Parse_diskstat()
{
        printf "Analyzing Diskstat data.. CWD : "
        pwd

        Print_filename $DISKSTAT_OUTPUT

        for (( i = 0; i < ${#DISKSTAT_ITEM[@]}; i++ ))
        do
                find . -name "[0-9][0-9]*" | sort | xargs sed -n -e '/^DISKSTAT_BEGIN/,/^DISKSTAT_END/ p' | grep "mmcblk0 " |
                awk -F " " -v cv="$(($i+4))" -v item="${DISKSTAT_ITEM[$i]}" -v iv="$(($i+1))" -v last="${#DISKSTAT_ITEM[@]}" '
                {
                        if (NR==1) {
                                printf "%s", item
                        }
                        printf ",%d", $cv
                }
                END {
                        if (iv!=last)
                                printf "\n"
                }' >> $DISKSTAT_OUTPUT
        done
}

function Parse_ionheap()
{
        printf "Analyzing IONHEAP data.. CWD : "
        pwd

        Print_filename $IONHEAP_OUTPUT

        for (( i = 0; i < ${#IONHEAP_ITEM[@]}; i++ ))
        do
                find . -name "[0-9][0-9]*" | sort | xargs sed -n -e '/^IONHEAP_BEGIN/,/^IONHEAP_END/ p' | grep "${IONHEAP_ITEM[$i]}" |
                awk -v item="${IONHEAP_ITEM[$i]}" -v iv="$(($i+1))" -v last="${#IONHEAP_ITEM[@]}" '
                {
                        if (NR==1) {
                                printf "%s", item
                        }
						if (item=="total orphaned") {
							printf ",%d", $3
						} else {
							printf ",%d", $2
						}
                }
                END {
                        if (iv!=last)
                                printf "\n"
                }' >> $IONHEAP_OUTPUT
        done
}

init
for (( ii = 0; ii < ${#RESULT[@]}; ii++ ))
do
	cd $OUTPUT/${RESULT[$ii]}
	Parse_meminfo
	Parse_ss RSS $RESULT_RSS
	Parse_ss PSS $RESULT_PSS
	Parse_ss USS $RESULT_USS
	Parse_process
	Parse_launchtime
	Parse_page
	Parse_cpuusage
	Parse_stat
	Parse_diskstat
	Parse_ionheap
	rm tmp*
done
