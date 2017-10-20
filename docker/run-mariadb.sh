#!/bin/bash

VOLUME_HOME="/var/lib/mysql"  
if find ${VOLUME_HOME} -maxdepth 0 -empty | read v; then  
    echo " -> Setting up new installation in $VOLUME_HOME"
    echo " -> Installing MariaDB"
    mysql_install_db > /dev/null 2>&1
    echo " -> MySQL Setup Done!"
    /create-mariadb-user.sh
    echo " -> DB Setup Done!"
else  
    echo "-> Booting on existing volume!"
fi

exec mysqld_safe  
