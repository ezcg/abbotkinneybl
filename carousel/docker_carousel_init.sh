#!/bin/bash

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

#echo "127.0.0.1 carousel.ezcg.local" >> /etc/hosts

echo "set number" > /root/.vimrc
echo "alias ll='ls -ltra'" >> /root/.bashrc
# timezone setup
echo TZ=America/Los_Angeles >> /etc/environment
rm -f /etc/localtime && ln -nsf /usr/share/zoneinfo/America/Los_Angeles /etc/localtime

# The following policy-rc.d update is from: https://forums.docker.com/t/error-in-docker-image-creation-invoke-rc-d-policy-rc-d-denied-execution-of-restart-start/880
echo "#!/bin/bash\nexit 0\n" > /usr/sbin/policy-rc.d

cd /var/app/current && npm install && npm run dev