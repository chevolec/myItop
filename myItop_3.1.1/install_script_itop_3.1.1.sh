#!/bin/bash
## https://sourceforge.net/projects/itop/files/itop/
## itop-7102.cbcfeoowso1q.us-east-1.rds.amazonaws.com
## !A$PwYN4m7mstGRO


sudo apt-get update
sudo apt -y install software-properties-common
sudo add-apt-repository  ppa:ondrej/php
sudo add-apt-repository  ppa:ondrej/apache2
sudo add-apt-repository  deb https://ppa.launchpadcontent.net/ondrej/apache2/ubuntu/ jammy main
sudo apt-get update

sudo apt-get install -y apache2 mariadb-server php8.1 php8.1-mysql php8.1-ldap php8.1-cli php8.1-soap graphviz php8.1-xml php8.1-gd php8.1-zip php8.1-fpm php8.1-mbstring php8.1-common php8.1-curl libapache2-mod-php8.1 acl unzip php-xml

 
wget https://sourceforge.net/projects/itop/files/itop/3.1.1-1/iTop-3.1.1-1-12561.zip/download -O /tmp/itop.zip 

sudo mkdir /var/www/html/itop
sudo unzip /tmp/itop.zip "web/*" -d /var/www/html/itop
sudo mv /var/www/html/itop/web/*  /var/www/html
sudo rm -fr /var/www/html/itop
sudo rm -fr /var/www/html/index.html
#sudo setfacl -dR -m u:"www-data":rwX /var/www/html/data /var/www/html/log
#sudo setfacl -R -m u:"www-data":rwX /var/www/html/data /var/www/html/log
sudo mkdir /var/www/html/env-production /var/www/html/env-production-build
sudo chown www-data:www-data /var/www/html/env-production /var/www/html/env-production-build


sudo cp artifacts/scripts/setup-itop-cron.sh /setup-itop-cron.sh
sudo cp artifacts/scripts/make-itop-config-writable.sh /make-itop-config-writable.sh
sudo cp artifacts/scripts/make-itop-config-read-only.sh /make-itop-config-read-only.sh
sudo cp artifacts/scripts/install-toolkit.sh /install-toolkit.sh
sudo cp artifacts/apache2.fqdn.conf /etc/apache2/conf-available/fqdn.conf
# sudo cp artifacts/create-mysql-admin-user.sh /create-mysql-admin-user.sh
sudo cp artifacts/run.sh /run.sh

sudo chown -R www-data:www-data /var/www/html

sudo cp -r service /etc/service
sudo chmod +x -R /etc/service

sudo service apache2 restart