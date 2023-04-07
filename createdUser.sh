#!/bin/bash

# Prompt for the new username
username=$1
password=$2
serveurName=$3
config=`sudo sed "s/placeholder/$serveurName/g" /etc/nginx/sites-available/template`

# Create the user with a home directory
echo `sudo useradd -m $username`

# Set the user's password
echo "$username:$password" | sudo chpasswd

# Print the username and password
echo "Username: $username"
echo "Your account has been created"

echo $config | sudo tee /etc/nginx/sites-available/$serveurName > /dev/null
sudo cp -r /etc/nginx/sites-available/$serveurName /etc/nginx/sites-enabled
echo "it's work"