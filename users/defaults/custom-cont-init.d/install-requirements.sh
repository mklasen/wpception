#!/bin/bash

echo "**** installing requirements ****"

# This is what make vscode remote-ssh work
apk add gcompat libstdc++ curl git php7 php7-fpm php7-opcache php7-phar php7-gd php7-mysqli php7-zlib php7-curl php7-json

# As alpine by default use busybox and some common utilities behave differently, like grep
apk add grep dropbear-scp dropbear-ssh

# Add zsh if using zsh shell
apk add zsh

# chsh -s $(which zsh)

# echo "**** Install oh-my-zsh ****"
# sh -c "$(curl -fsSL https://raw.github.com/ohmyzsh/ohmyzsh/master/tools/install.sh)"

# git clone --depth 1 -- https://github.com/marlonrichert/zsh-snap.git
# source zsh-snap/install.zsh
# znap install ohmyzsh/ohmyzsh


curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp