#!/bin/bash

DB_NAME="$1"
USER_NAME="$2"
USER_PASSWORD="$3"

# Check if the user already exists
if [ -n "$(mysql -uroot -se "SELECT User FROM mysql.user WHERE User='$USER_NAME'")" ]; then
  	echo "User '$USER_NAME' already exists"
	mysql -uroot -e "CREATE DATABASE $DB_NAME;"
	mysql -uroot -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$USER_NAME'@'localhost';"
	exit 1
fi

# Create the database
mysql -uroot -e "CREATE DATABASE $DB_NAME;"

# Create the user
mysql -uroot -e "CREATE USER '$USER_NAME'@'localhost' IDENTIFIED BY '$USER_PASSWORD';"

# Grant privileges
mysql -uroot -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$USER_NAME'@'localhost';"

echo "Database and user created successfully"