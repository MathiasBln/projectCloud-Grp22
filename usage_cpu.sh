#!/bin/bash

PREV_TOTAL=0
PREV_IDLE=0

# Get the total CPU statistics, discarding the 'cpu ' prefix.
CPU=($(sed -n 's/^cpu\s//p' /proc/stat))
IDLE=${CPU[3]} # Just the idle CPU time.

# Calculate the total CPU time.
TOTAL=0
for VALUE in "${CPU[@]:0:8}"; do
    TOTAL=$((TOTAL+VALUE))
done

# Calculate the CPU usage since we last checked.
DIFF_IDLE=$((IDLE-PREV_IDLE))
DIFF_TOTAL=$((TOTAL-PREV_TOTAL))
DIFF_USAGE=$(((1000*(DIFF_TOTAL-DIFF_IDLE)/DIFF_TOTAL+5)/10))


PREV_TOTAL="$TOTAL"
PREV_IDLE="$IDLE"

sudo mysql -uroot -e "USE megabonnesmeufs_db; UPDATE usage_machine SET content = '$DIFF_USAGE%' WHERE title = 'CPU';"