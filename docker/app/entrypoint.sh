#!/bin/bash

rm -f /var/run/apache2/apache2.pid

# Run Supervisord
exec /usr/bin/supervisord -c /etc/supervisord.conf
