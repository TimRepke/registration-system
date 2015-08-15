Registration System
===================

This system was developed for the computer science students union at the Humboldt-Universität zu Berlin to organise our
annual freshers weekend trips. Since the attendance grew over the years we needed a powerful system that can handle
all the registrations, messaging and a powerful admin panel to plan costs, take notes, manage waiting list and registration
and much more.


# Features

- multiple methods for the signup process
- cost planning (for collecting and redistributing leftover money)
- ...

# Basic usage

### Create a new trip

TODO

### Link to send to the freshers 

TODO

### Status of a signup

TODO

### Play-only mode

coming soon! (because the stuff is too good to just open it for one signup)

### Add new admin
Send out the link to `passwd/index.html` to the new user. They'll send their encrypted password back. Add that 
(in the appropriate structure) to the `passwd/users.txt` file.

# Project structure

There are two files that sort of handle the configuration because we couldn't be bothered to create another
database table:

- `./passwd/users.txt` add users to this file. `S` stands for super admin (can create new trips), `N` users can only manage the current trip
- `./config_current_fahrt_id` (a file that only contains the id of the current trip

Folder structure:

- The `admin` folder contains everything for the admin backend.
- `view` contains all frontend stuff
- `view/signups` contains all possible signup methods (games, add them to `config.inc.php`)
- `view/js` should contain all commonly used js things
- ...

# How to deploy

### Server requirements

 - PHP 5.5.x (tested with 5.5.3)<br />
 - MySQL (other databases might work, check the medoo framework)

### Database setup
Check out the folger `other/sqlDumps`. There is an SQL file that creates the database. We also tried to make sure
to provide migrations in case updates are needed.

#### Configuration

Make adjustments in `config.inc.php`

 - Database parameters (`$config_db`)
 - Set base URL (`$config_baseurl`)

### File permissions

Adjust CHMOD for
 
- config_current_fahrt_id (rw-rw-rw-)
- passwd/users.txt (rw-rw-rw-)

# Further notes

You could add the the config file in your local gitignore file to prevent overrides on updates.
In case you do that, remember to check for necessary updates.