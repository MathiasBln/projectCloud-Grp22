#!/bin/bash


username=$1
password=$2
bdd=$3

if sudo tar -czvf /home/$username/backups/$username-backups.tar.gz /home/$username/uploads ; then
        echo "Backup des fichiers crée avec succès."
else
        echo "La commande de sauvegarde a échoué."
fi

if sudo mysqldump -u tutu -ptutu tutu > /home/tutu/backups/tutu-bdd_backup.sql ; then
	echo "bdd backup created"
else
	echo "error"
fi

