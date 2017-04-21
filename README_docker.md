# Docker Environment Setup

## Setup And Using The Environment

### Step 1
Ensure database dump path inside docker-compose.yml points to the newest dump

### Step 2
Change database connection inside
registration-system/config.local.php
from "localhost" to "db"

### Step 3
Build and start up the containers
```
docker-compose build
docker-compose up -d
```

### Step 4
Wait for containers to start up.

Run a bash inside the web container (`docker exec -it registrationsystem_web_1 bash`) and
```
cd usr/share/nginx/html/
echo -n 2 > config_current_fahrt_id
chmod 777 config_current_fahrt_id 
cp passwd/users.example.txt passwd/users.txt
```

Open http://localhost:8080 in your browser

Admin interface is at http://localhost:8080/admin (login with sudo:password)

For phpMyAdmin find the IP it runs on (`docker inspect registrationsystem_phpmyadmin_1` and find IPAddress) and open
http://<ip>:8090

## Mysql Dump Upgrade
Remove containers as in cleanup, remove mysql_dump folder and start again

## Cleanup
Run:
```
docker-compose stop
docker-compose rm
```
Then remove mysql_data folder

