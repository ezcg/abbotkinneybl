FROM ubuntu:18.04

RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
RUN apt-get update && apt-get install -qq -y --no-install-recommends apt-utils locales build-essential libaio1 \
sysstat zip unzip ntp awscli iptables apache2 rsyslog mailutils git-core locate mysql-client iputils-ping telnet npm wget \
curl vim net-tools cron openssh-client composer sudo \
php7.2 \
php-pear \
php-xdebug \
libapache2-mod-php \
php7.2-mysql \
php7.2-mbstring \
php7.2-bcmath \
php7.2-mysql \
php7.2-soap \
php7.2-gd \
php7.2-opcache \
php7.2-cli \
php7.2-imap \
php7.2-common \
php7.2-interbase \
php7.2-phpdbg \
php7.2-xml \
php-curl \
php7.2-intl \
php7.2-xmlrpc \
php7.2-json \
php7.2-ldap \
php7.2-zip

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid
ENV COMPOSER_ALLOW_SUPERUSER 1

# set locale
RUN locale-gen "en_US.UTF-8"
RUN echo LC_ALL="en_US.UTF-8" >> /etc/default/locale
RUN echo LC_CTYPE="en_US.UTF-8" >> /etc/default/locale
RUN echo LANG="en_US.UTF-8" >> /etc/default/locale
RUN echo LC_ALL="en_US.UTF-8" >> /etc/environment
RUN echo LC_CTYPE="en_US.UTF-8" >> /etc/environment
RUN echo LANG="en_US.UTF-8" >> /etc/environment
ENV LC_CTYPE="en_US.UTF-8"
ENV LC_ALL="en_US.UTF-8"
ENV LANG="en_US.UTF-8"

# timezone setup
RUN echo TZ=America/Los_Angeles >> /etc/environment
RUN rm -f /etc/localtime && ln -nsf /usr/share/zoneinfo/America/Los_Angeles /etc/localtime

# install redis
#RUN pecl install redis-3.1.2
#RUN echo -e "extension=redis.so" > /etc/php/7.0/cli/conf.d/redis.ini

# The following policy-rc.d update is from: https://forums.docker.com/t/error-in-docker-image-creation-invoke-rc-d-policy-rc-d-denied-execution-of-restart-start/880
RUN echo "#!/bin/bash\nexit 0\n" > /usr/sbin/policy-rc.d

#a2enmod mcrypt and sudo a2enmod mbstring
#RUN phpenmod mcrypt
RUN a2enmod proxy
RUN a2enmod proxy_http
RUN a2enmod headers
RUN a2enmod rewrite
RUN a2enmod ssl

# get rid of notice:
# AH00558: apache2: Could not reliably determine the server's fully qualified domain name,
# using 127.0.0.1. Set the 'ServerName' directive globally
RUN grep -qF -- "ServerName localhost" "/etc/apache2/apache2.conf" || echo "ServerName localhost" >> "/etc/apache2/apache2.conf"

#CMD ["apache2ctl", "-D","FOREGROUND"]

#When you define the entrypoint, all commands after will be arguments, in this case to your bash.
#ENTRYPOINT ["/bin/bash"]

# On centos, ls command inside docker caused 'permission denied'. To fix, outside of docker, run chcon -Rt svirt_sandbox_file_t /home/matt/ezcgbe

# Add user for laravel application
#RUN groupadd -g 1000 www
#RUN useradd -u 1000 -ms /bin/bash -g www www
# Copy existing application directory permissions
#COPY --chown=www:www . /var/app/current
# Change current user to www
#USER www

# If this is not set, 'eb create' will fail with "No EXPOSE directive found in Dockerfile, abort deployment"
EXPOSE 80

