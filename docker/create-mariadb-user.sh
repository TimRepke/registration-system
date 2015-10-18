#!/bin/bash
/usr/bin/mysqld_safe > /dev/null 2>&1 &

RET=1
while [[ RET -ne 0 ]]; do
    sleep 5
    mysql -uroot -e "status" > /dev/null 2>&1
    RET=$?
done

mysql -uroot -e "CREATE USER '$MYSQL_USER'@'%' IDENTIFIED BY '$MYSQL_PASSWORD'"
mysql -uroot -e "GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_USER'@'%' WITH GRANT OPTION"
mysql -uroot -e "CREATE DATABASE $MYSQL_DATABASE"
echo "   -> SQL Dump Import ..."
mysql -uroot $MYSQL_DATABASE < /dump.sql
echo "   -> SQL Dump OK"

mysqladmin -uroot shutdown
