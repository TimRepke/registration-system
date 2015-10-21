#!/usr/bin/env bash
set -e

function install {
    echo "starting install wizzard"

    # sure?
    echo "this might destroy your current installation (if existing)"
    read -r -p "Are you sure you want to proceed? [y/N] " response
    response=${response,,}    # tolower
    if [[ ! $response =~ ^(yes|y)$ ]] ; then exit 1 ; fi

    # change operation dir
    cd "${BASH_SOURCE%/*}"
    mkdir -p backups

    # create last update file
    date +"%Y%m%d" > backups/lastUpdate

    # create required files
    cp ../passwd/users.example.txt ../passwd/users.txt
    echo "2" > ../config_current_fahrt_id

    # adjust chmod
    chmod -R 644 ../
    chmod 777 ../config_current_fahrt_id
    chmod 777 ../passwd/users.txt

    # ask config stuff
    read -p "baseurl (i.e. \"https://domain.com/fsfahrt\"): " base
    read -p "database name: " dbname
    read -p "database user: " dbuser
    read -p "database pw: " dbpass
    read -p "database host: " dbhost

    # adapt config.local.php file

    # run db init

}

function update {
    echo "updating system"

    # sure?
    echo "this might destroy your current installation (if existing)"
    read -r -p "Are you sure you want to proceed? [y/N] " response
    response=${response,,}    # tolower
    if [[ ! $response =~ ^(yes|y)$ ]] ; then exit 1 ; fi

    # change operation dir
    cd "${BASH_SOURCE%/*}"
    mkdir -p backups

    # backup config files
    cp ../config_current_fahrt_id backups/
    cp ../passwd/users.txt backups/
    cp ../config.local.php backups/

    # fetch from github
    read -p "git branch to pull from [default: master]: " BRANCH
    read -p "git origin to pull from [default: origin]: " ORIGIN
    if [[ -z $BRANCH ]] ; then BRANCH=master ; fi
    if [[ -z $ORIGIN ]] ; then ORIGIN=origin ; fi
    git checkout $BRANCH
    git pull $ORIGIN $BRANCH

    # fetch db credentials
    dbname=`cat backups/config.local.php | grep \"name\" | cut -d \" -f 4`
    dbuser=`cat backups/config.local.php | grep \"user\" | cut -d \" -f 4`
    dbpass=`cat backups/config.local.php | grep \"pass\" | cut -d \" -f 4`
    dbhost=`cat backups/config.local.php | grep \"host\" | cut -d \" -f 4`

    # get last update date
    lastUpdate=`cat backups/lastUpdate`
    today=`date +"%Y%m%d"`

    # create db backup
    mysqldump -h $dbhost -u $dbuser -p $dbpass $dbname > backups/backup_$today.sql

    # look for pending SQL updates and execute
    for update in sqlDumps/update_*.sql; do
        if [ $lastUpdate -lt `echo $update | cut -d _ -f 2 | cut -d . -f 1` ] ; then
            echo "applying update $update"
            mysql -h $dbhost -u $dbuser -p $dbpass $dbname < $update
        else
            echo "already up to date with $update"
        fi
    done

    # update last update file
    echo $today > backups/lastUpdate

    # move config files back
    mv backups/config_current_fahrt_id ../
    mv backups/config.local.php ../
    mv backups/users.txt ../passwd/users.txt
}

function printHelp {
    echo "Script usage"
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