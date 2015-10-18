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
Open http://localhost:8080 in your browser

## Mysql Dump Upgrade
Stop containers, remove mysql_dump folder and start again

## Cleanup
Run:
```
docker-compose stop
docker-compose rm
```
Then remove mysql_data folder

