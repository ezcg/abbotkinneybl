#!/bin/bash

printf "\nwhoami: "
whoami

/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install.sh)"
echo 'eval $(/home/linuxbrew/.linuxbrew/bin/brew shellenv)' >> /home/linuxbrew/.profile
eval $(/home/linuxbrew/.linuxbrew/bin/brew shellenv)
brew install mkcert
mkcert -install
