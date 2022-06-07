#!/bin/bash
set -e

# Stream edit the file replacing mysql tokens with environment variables
sed -e "s/MYSQL_USER/${MYSQL_USER}/g" -e "s/MYSQL_PASSWORD/${MYSQL_PASSWORD}/g" -e "s/MYSQL_DATABASE/${MYSQL_DATABASE}/g" /tmp/local.xml > /var/www/web/app/etc/local.xml

# Copy SSH private key to .ssh folder
mkdir -p /root/.ssh
cp /tmp/.ssh/id_rsa /root/.ssh/id_rsa
chmod 600 /root/.ssh/id_rsa

# Include bitbucket.org in known_hosts file 
touch /root/.ssh/known_hosts
ssh-keyscan bitbucket.org > /root/.ssh/known_hosts
sleep 2

exec "$@"
