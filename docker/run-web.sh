#!/bin/bash

sed -ri '
/^php_flag\[display_errors\]/ d
$ a php_flag[display_errors] = on
' /usr/local/etc/php-fpm.conf

sed -ri '
/^;?error_log/ c error_log = syslog
/^;?syslog.facility/ c syslog.facility = daemon
/^;?syslog.ident/ c syslog.ident = php-fpm
/^;?log_level/ c log_level = notice
' /usr/local/etc/php-fpm.d/*.conf

tee /usr/local/etc/php/conf.d/zzz-registration-system.ini << END
[PHP]
error_reporting = E_ALL & ~E_NOTICE
display_errors = On
display_startup_errors = On
log_errors = On
ignore_repeated_errors = Off
ignore_repeated_source = Off
track_errors = On
html_errors = On
END

exec /start.sh
