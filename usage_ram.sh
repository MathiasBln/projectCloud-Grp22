#!/bin/bash

USAGE_RAM=`free | awk 'NR==2{printf "%.2f%%\n", $3*100/$2 }'`

sudo mysql -uroot -e "USE megabonnesmeufs_db; UPDATE usage_machine SET content = '$USAGE_RAM' WHERE title = 'RAM';"