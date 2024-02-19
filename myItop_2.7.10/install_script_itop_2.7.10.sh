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

sudo apt-get install -y apache2 mariadb-server php7.4 php7.4-mysql php7.4-ldap php7.4-cli php7.4-soap php7.4-json graphviz php7.4-xml php7.4-gd php7.4-zip php7.4-fpm php7.4-mbstring php7.4-common libapache2-mod-php7.4 acl unzip graphviz

 
# wget https://sourceforge.net/projects/itop/files/latest/download -O /tmp/itop.zip
wget https://sourceforge.net/projects/itop/files/itop/2.7.10/iTop-2.7.10-12681.zip/download -O /tmp/itop.zip 

# wget https://sourceforge.net/projects/itop/files/itop/2.7.1/iTop-2.7.1-5896.zip/download -O /tmp/itop.zip 

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

##  Fix the service family Typo
sudo sed -ie 's/servicefamilly/servicefamily/g' /var/www/html/datamodels/2.x/itop-service-mgmt-provider/datamodel.itop-service-mgmt-provider.xml
sudo sed -ie 's/ServiceFamilly/ServiceFamily/g' /var/www/html/datamodels/2.x/itop-service-mgmt-provider/module.itop-service-mgmt-provider.php

##  Remove backupfiles
sudo rm /var/www/html/datamodels/2.x/itop-service-mgmt-provider/datamodel.itop-service-mgmt-provider.xmle /var/www/html/datamodels/2.x/itop-service-mgmt-provider/module.itop-service-mgmt-provider.phpe

## Copy Extensions
## ## Service Family
sudo cp extensions/service-family-request.zip /var/www/html/extensions/
sudo unzip -q /var/www/html/extensions/service-family-request.zip -d /var/www/html/extensions/
sudo rm /var/www/html/extensions/service-family-request.zip
## ## No se que sea
sudo cp extensions/cosmocel_v1.0.5.zip /var/www/html/extensions/
sudo unzip -q /var/www/html/extensions/cosmocel_v1.0.5.zip -d /var/www/html/extensions/
sudo rm /var/www/html/extensions/cosmocel_v1.0.5.zip
## ## Responsiva 
sudo cp extensions/Responsiva_v1.zip /var/www/html/extensions/
sudo unzip -q /var/www/html/extensions/Responsiva_v1.zip -d /var/www/html/extensions/
sudo cp /var/www/html/extensions/Responsiva_v1/responsiva.php /var/www/html/pages/
sudo rm /var/www/html/extensions/Responsiva_v1.zip


sudo chown -R www-data:www-data /var/www/html

sudo cp artifacts/importLdapScript.php /var/www/html/pages/

sudo cp -r service /etc/service
sudo chmod +x -R /etc/service

sudo service apache2 restart