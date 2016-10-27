#!/usr/bin/env bash
set -e

function install {
    echo "starting install wizzard"

    # sure?
    echo "this might destroy your current installation (if existing)"
    read -r -p "Are you sure you want to proceed? [y/N] " response
    response=${response,,}    # tolower
    if [[ ! $response =~ ^(yes|y)$ ]] ; then exit 1 ; fi

    echo "[1/7] initialising"

    # change operation dir
    cd "${BASH_SOURCE%/*}"
    mkdir -p backups

    # create last update file
    date +"%Y%m%d" > backups/lastUpdate

    # create required files
    echo "[2/7] setting up files"
    cp ../passwd/users.example.txt ../passwd/users.txt
    echo "2" > ../config_current_fahrt_id

    # adjust chmod
    echo "[3/7] setting chmod"
    chmod -R 644 ../
    chmod 777 ../config_current_fahrt_id
    chmod 777 ../passwd/users.txt

    # ask config stuff
    echo "[4/7] some config params, please..."
    read -p "baseurl (i.e. \"https://domain.com/fsfahrt\"): " base
    read -p "database name: " dbname
    read -p "database user: " dbuser
    read -p "database pw: " dbpass
    read -p "database host: " dbhost

    # adapt config.local.php file
    echo "[5/7] writing config file"
    sed -i "s/($var = \")[^\"]*/\1$base/" ../config.local.php
    sed -i "s/(\"dbname\" => \")[^\"]*/\1$dbname/" ../config.local.php
    sed -i "s/(\"dbuser\" => \")[^\"]*/\1$dbuser/" ../config.local.php
    sed -i "s/(\"dbpass\" => \")[^\"]*/\1$dbpass/" ../config.local.php
    sed -i "s/(\"dbhost\" => \")[^\"]*/\1$dbhost/" ../config.local.php

    # get init sql files
    inits=()
    echo "[6/7] available init files:"
    for initsql in sqlDumps/init_*.sql; do
        inits=("${inits[@]}" "$initsql")
        echo "${#inits[@]}) $initsql"
    done

    # run db init
    echo "[7/7] select init dump or kill script to do it manually"
    read -p "which dump do you want (latest recommended): " dump
    mysql -h $dbhost -u $dbuser -p $dbpass $dbname < ${inits[$dump]}

    exit 0
}

function update {
    echo "updating system"

    # sure?
    echo "this might destroy your current installation (if existing)"
    read -r -p "Are you sure you want to proceed? [y/N] " response
    response=${response,,}    # tolower
    if [[ ! $response =~ ^(yes|y)$ ]] ; then exit 1 ; fi

    if [[ ! -f backups/lastUpdate ]] ; then
        read -r -p "Last update or install? As 'Y-m-d' " response
        echo response > backups/lastUpdate
    fi


    # change operation dir
    cd "${BASH_SOURCE%/*}"
    mkdir -p backups

    # backup config files
    echo "backing up config files"
    cp ../config_current_fahrt_id backups/
    cp ../passwd/users.txt backups/
    cp ../config.local.php backups/

    # fetch from github
    read -p "git branch to pull from [default: master]: " BRANCH
    read -p "git origin to pull from [default: origin]: " ORIGIN
    if [[ -z $BRANCH ]] ; then BRANCH=master ; fi
    if [[ -z $ORIGIN ]] ; then ORIGIN=origin ; fi

    # create backup
    echo "creating backup on 'backup' branch"
    git checkout -B backup
    git add --all
    git commit --allow-empty -m "backup from `date +"%d.%m.%Y"`"

    # pull from origin
    echo "force pulling from $ORIGIN/$BRANCH"
    git checkout $BRANCH
    git fetch $ORIGIN
    git reset --hard $ORIGIN/$BRANCH

    # fetch db credentials
    echo "fetching db config"
    dbname=`cat backups/config.local.php | grep \"name\" | cut -d \" -f 4`
    dbuser=`cat backups/config.local.php | grep \"user\" | cut -d \" -f 4`
    dbpass=`cat backups/config.local.php | grep \"pass\" | cut -d \" -f 4`
    dbhost=`cat backups/config.local.php | grep \"host\" | cut -d \" -f 4`

    # get last update date
    lastUpdate=`cat backups/lastUpdate`
    today=`date +%F`

    # create db backup
    echo "backing up database"
    mysqldump -h $dbhost -u $dbuser -p $dbpass $dbname > backups/backup_$today.sql

    # look for pending SQL updates and execute
    echo "updating database"
    for update in sqlDumps/update_*.sql; do
        if [ $lastUpdate -lt `echo $update | cut -d _ -f 2 | cut -d . -f 1` ] ; then
            echo "applying update $update"
            mysql -h $dbhost -u $dbuser -p $dbpass $dbname < $update
        else
            echo "already up to date with $update"
        fi
    done

    echo "finishing up update"

    # update last update file
    echo $today > backups/lastUpdate

    # move config files back
    mv backups/config_current_fahrt_id ../
    mv backups/config.local.php ../
    mv backups/users.txt ../passwd/users.txt

    echo "Please check whether the format of config files has changed..."
    echo "Now get a coffee and add syntactic sugar~!"

    exit 0
}

function printHelp {
    echo "Script usage"
    echo "-h | --help echo this help"
    echo "-u | --update to update the system"
    echo "-i | --install to install the system"
}

if [[ $# -eq 0 ]]; then
    echo "No param given"
    printHelp
else
    while [[ $# -gt 0 ]];
    do
        opt="$1"
        shift;
        case "$opt" in
            "-h" | "--help" )
                printHelp
                exit 0;;
            "-u" | "--update" )
                update ;;
            "-i" | "--install"   )
                install ;;
            * )
                echo "Unexpected option ${opt}"
                printHelp
                exit 1;;
        esac
    done
fi