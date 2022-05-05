# source /extras/wp-completion.bash

sh -c "$(curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh)" "" --unattended
sed -i -e 's/plugins=(git)/plugins=(git wp-cli)/g' .zshrc
zsh
# source .zshrc
cd web