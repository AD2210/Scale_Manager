#!/bin/bash

set -e

echo "🔧 Mise à jour du système..."
sudo apt update && sudo apt upgrade -y

echo "📦 Installation des dépendances..."
sudo apt install -y ca-certificates curl gnupg lsb-release

echo "🔑 Ajout de la clé GPG Docker..."
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/debian/gpg | \
    sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg

echo "📚 Ajout du dépôt Docker officiel..."
echo \
  "deb [arch=$(dpkg --print-architecture) \
  signed-by=/etc/apt/keyrings/docker.gpg] \
  https://download.docker.com/linux/debian \
  $(lsb_release -cs) stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

echo "🔄 Mise à jour des sources APT..."
sudo apt update

echo "🐳 Installation de Docker Engine + Compose plugin..."
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

echo "✅ Test Docker avec hello-world..."
sudo docker run hello-world

echo "🛡️ Ajout de l'utilisateur actuel au groupe 'docker' (optionnel)..."
sudo usermod -aG docker $USER
echo "🔁 Veuillez exécuter 'newgrp docker' ou redémarrer votre terminal pour appliquer les permissions."

echo "🎉 Installation terminée !"
