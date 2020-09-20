#!/bin/bash

apt-get -y update
apt-get -y install vim libnss3-tools curl git build-essential procps sudo
echo "alias ll='ls -ltra'" >> /root/.bashrc
echo "set number" >> /root/.vimrc
echo "fastcgi_read_timeout 300;" > /etc/nginx/conf.d/fastcgi_read_timeout.conf
echo "proxy_read_timeout 300;" > /etc/nginx/conf.d/proxy_read_timeout.conf

#userExists=$(grep -c '^linuxbrew:' /etc/passwd)
#if [ "$userExists" == 0 ];then
#  printf "\nAdding user linuxbrew\n"
#  groupadd -g 1000 linuxbrew
#  useradd --create-home --shell /bin/bash -m -u 1000 linuxbrew -g linuxbrew
#  adduser linuxbrew sudo
#  echo '%sudo ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers
#else
#  printf "\nUser linuxbrew already exists\n"
#fi



tail -f /dev/null
