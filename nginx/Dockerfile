FROM jwilder/nginx-proxy

RUN apt-get -y update
RUN apt-get -y install vim libnss3-tools curl git build-essential procps sudo
RUN echo "alias ll='ls -ltra'" >> /root/.bashrc
RUN echo "set number" >> /root/.vimrc
RUN echo "fastcgi_read_timeout 300;" > /etc/nginx/conf.d/fastcgi_read_timeout.conf
RUN echo "proxy_read_timeout 300;" > /etc/nginx/conf.d/proxy_read_timeout.conf
