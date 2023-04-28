USAGE_HDD=`df -h | awk '{if ($6 == "/") print $5}'`

sudo mysql -uroot -e "USE megabonnesmeufs_db; UPDATE usage_machine SET content = '$USAGE_HDD' WHERE title = 'HDD';"