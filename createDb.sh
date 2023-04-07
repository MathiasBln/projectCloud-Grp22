sudo mysql -e "CREATE DATABASE $1;"
sudo mysql -e "CREATE USER '$2'@'localhost' IDENTIFIED BY '$3';"
sudo mysql -e "GRANT ALL PRIVILEGES ON $1.* TO '$2'@'localhost';"

echo "BDD CREATED"