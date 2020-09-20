#!/bin/bash

. /var/app/current/base.sh

SITE=$1
ENVIRONMENT=$2
SITE_TYPE=$3
IS_QUICKSTART=$4
printf "\n\nRunning docker_init.sh\n\n"

if test -f /var/app/current/env/.env; then
  printf "\ndocker_init.sh .env FILE exists\n\n"
else
  printf "\n\n************************************\n"
  printf "\n\n************************************\n"
  printf "\n\n************************************\n"
  printf "\n\n************************************\n"
  printf "docker_init.sh /be/env/.env FILE does NOT exist. Create env file manually first. Aborting...\n\n"
  printf "\n\n************************************\n"
  printf "\n\n************************************\n"
  printf "\n\n************************************\n"
  printf "\n\n************************************\n"
  #exit
fi

if test -f "/etc/apache2/sites-enabled/${SITE}.apache.conf"; then
  rm /etc/apache2/sites-enabled/${SITE}.apache.conf
fi
if test -f "/etc/apache2/sites-enabled/000-default.conf"; then
  rm /etc/apache2/sites-enabled/000-default.conf
fi
ln -s /var/app/current/apache/${SITE}.apache.conf /etc/apache2/sites-enabled/${SITE}.apache.conf
echo "127.0.0.1 ${SITE}.local" >> /etc/hosts

rm /etc/php/7.2/mods-available/xdebug.ini
touch /tmp/xdebug.log
chmod 777 /tmp/xdebug.log
> /tmp/xdebug.log
cd /etc/php/7.2/mods-available
ln -s /var/app/current/xdebug.ini .

echo "set statusline=%f
set pastetoggle=<F2>
set smartcase
set number
set tabstop=4
set shiftwidth=4
set softtabstop=4
set smartindent
set autoindent
hi apacheComment guifg=white
set syntax=php
au BufRead,BufNewFile *.html,*.php,*.phtml,*.inc setfiletype php " > /root/.vimrc

touch /tmp/faileddomains
chmod 777 /tmp/faileddomains

if [[ "$ENVIRONMENT" = "cron" && "$SITE_TYPE" = "admin" ]];then
  crontab /var/app/current/cron/admin_localcrontab
  service cron restart
elif [[ "$ENVIRONMENT" = "cron" && "$SITE_TYPE" = "list" ]];then
  crontab /var/app/current/cron/list_localcrontab
  service cron restart
else
  printf "\nCron not started because environment is not 'cron'. See SETUP.txt to run crons.\n"
fi

cd /var/app/current
printf "\nWriting aws creds from env/.env to ~/.aws/credentials\n"
id=$(cat env/.env | grep -Po "AWS_ACCESS_KEY_ID=\K[^\s]+")
key=$(cat env/.env | grep -Po "AWS_SECRET_ACCESS_KEY=\K[^\s]+")
region=$(cat env/.env | grep -Po "AWS_DEFAULT_REGION=\K[^\s]+")
if [[ ! -d "/root/.aws" ]]; then
  printf "\n /root/.aws dir does not exist in docker container, making it...\n"
  mkdir ~/.aws
fi
printf "\nWriting aws creds to ~/.aws/credentials\n"
touch ~/.aws/credentials
echo "[default]" > ~/.aws/credentials
echo "aws_access_key_id = $id" >> ~/.aws/credentials
echo "aws_secret_access_key = $key" >> ~/.aws/credentials
echo "region = $region" >> ~/.aws/credentials

#userExists=$(grep -c '^dockeruser:' /etc/passwd)
#if [ "$userExists" == 0 ];then
#  printf "\nAdding user dockeruser\n"
#  groupadd -g 1000 dockeruser && useradd --create-home --shell /bin/bash -r -u 1000 -g dockeruser dockeruser
#else
#  printf "\nUser dockeruser already exists\n"
#fi
touch /var/app/current/storage/logs/laravel.log
chown www-data:www-data /var/app/current/storage/logs/laravel.log
cd /var/app/current/storage/framework/sessions && rm -fr *
cd /var/app/current/storage/framework/views/ && rm -fr *
chown -R www-data:www-data /var/app/current/storage
chmod 777 /var/app/current/bootstrap/cache

if [ -d "/var/app/current/vendor" ];then
    echo "vendor directory exists, but you may want to run composer update"
else
    echo "vendor directory does not exists. After docker brings everything up, be sure to run on the host machine:"
    printf "\n********************************************************************************"
    printf "\n********************************************************************************"
    printf "\n\ndocker exec -it admin bash -c 'cd /var/app/current && composer update'\n\n"
    printf "\n********************************************************************************"
    printf "\n********************************************************************************"
    #cd /var/app/current && composer update #this times out, just do it post
fi
chown -R 1000:1000 /var/app/current/vendor

cd /var/app/current/
bash clearcache.sh

printf "\nStarting Apache\n\n\n"
apache2ctl -D FOREGROUND
# once apache starts, nothing in this script after runs
printf "\nThis should never get printed to the terminal. If it has, something has gone wrong. \n"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\nERROR BURIED IN THE LOGS ABOVE"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"
printf "\n********************************************************************************"




