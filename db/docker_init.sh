#!/bin/bash

. ./base.sh

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

echo "alias ll='ls -ltra'" >> /root/.bashrc

bash demo_init.sh 2>&1 | grep -v "Using a password"

printf "\nDone\n\n"
