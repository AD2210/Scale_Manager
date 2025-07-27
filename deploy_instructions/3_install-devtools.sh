#!/bin/bash

set -e

echo "🔧 Mise à jour du système..."
sudo apt update && sudo apt upgrade -y

echo "📦 Ajout du dépôt ondrej/php pour PHP 8.4..."
sudo apt install -y lsb-release ca-certificates apt-transport-https software-properties-common gnupg2 curl
curl -fsSL https://packages.sury.org/php/apt.gpg | gpg --dearmor | sudo tee /etc/apt/trusted.gpg.d/ondrej.gpg > /dev/null
echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" | sudo tee /etc/apt/sources.list.d/ondrej-php.list

echo "🔄 Rafraîchissement des dépôts..."
sudo apt update

echo "🧩 Installation de PHP 8.4 + extensions nécessaires..."
sudo apt install -y php8.4 php8.4-cli php8.4-common \
  php8.4-intl php8.4-mbstring php8.4-zip php8.4-curl \
  php8.4-opcache php8.4-apcu \
  php8.4-mysql php8.4-pgsql

echo "🛠️ Installation de Git..."
sudo apt install -y git

echo "📦 Installation de Composer..."
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer

echo "📦 Installation du Symfony CLI..."
curl -sS https://get.symfony.com/cli/installer | bash
export PATH="$HOME/.symfony/bin:$PATH"
echo 'export PATH="$HOME/.symfony/bin:$PATH"' >> ~/.bashrc

echo "📦 Installation des packages net-tools et lsof..."
sudo apt update
sudo apt install -y net-tools lsof

# recharger le profile pour les commandes symfony
source ~/.bashrc || true

echo "✅ Vérification des versions installées..."

composer --version

# → Pour vérifier la version d'un projet Symfony, on doit être dans le projet Symfony
#    et exécuter `php bin/console --version`
echo "Pour vérifier la version Symfony d’un projet :"
echo "cd dans le dossier du projet et exécute : php bin/console --version"

echo "🎉 Installation terminée."
