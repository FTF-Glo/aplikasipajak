#!/bin/bash
set -e

#start apache service
#service apache2 start

#enable site
a2ensite 9pajak.conf
a2ensite pbb.conf
a2ensite bphtb.conf
a2ensite payment.conf

#reload service
service apache2 reload || true

# Start Apache in the foreground
apachectl -D FOREGROUND