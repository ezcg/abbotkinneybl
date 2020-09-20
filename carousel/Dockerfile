FROM ubuntu:18.04

RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections
RUN apt-get update && apt-get install -qq -y --no-install-recommends apt-utils locales build-essential libaio1 \
sysstat zip unzip ntp awscli iptables locate iputils-ping telnet npm wget \
curl vim net-tools composer gnupg telnetd

RUN curl -sL https://deb.nodesource.com/setup_12.x | bash -
RUN apt-get install -y nodejs lsof
RUN npm install nodemon -g

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


#When you define the entrypoint, all commands after will be arguments, in this case to your bash.
#ENTRYPOINT ["/bin/bash"]

