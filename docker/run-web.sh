#!/bin/bash

# fix php-fpm
sed -ri '
/^php_flag\[display_errors\]/ d
' /usr/local/etc/php-fpm.conf

# check if our changes were applied yet
if ! grep -E '^\[program:sysklogd\]$' /etc/supervisord.conf >&/dev/null; then
    # fix php-fpm configs
    sed -ri '
/^;?error_log/ c error_log = syslog
/^;?syslog.facility/ c syslog.facility = daemon
/^;?syslog.ident/ c syslog.ident = php-fpm
/^;?log_level/ c log_level = notice
' /usr/local/etc/php-fpm.d/*.conf

    # add our own logging configuration
    tee /usr/local/etc/php/conf.d/zzz-registration-system.ini >&/dev/null << END
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

    # fix nginx config access_log/error_log targets
    sed -ri \
    's/^([ \t]*)((access|error)_log) .*$/\1\2 syslog:server=unix:\/dev\/log,facility=local7,tag=nginx;/' \
    /etc/nginx/sites-enabled/* /etc/nginx/nginx.conf

    # cleanup logging options in supervisord (startup)
    sed -ri '/^std(err|out)_.*=/ d' /etc/supervisord.conf
    # add syslogger entry at the end of supervisord config
    tee -a /etc/supervisord.conf >&/dev/null <<END
[program:sysklogd]
command = /usr/sbin/syslogd -d
autostart=true
autorestart=true
priority=1
END
fi

exec /start.sh
