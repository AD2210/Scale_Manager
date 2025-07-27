#!/bin/bash
set -e

# Usage: ./clone-symfony-project.sh <repo-url> <destination-dir>
if [ "$#" -ne 2 ]; then
  echo "Usage : $0 <git repo url> <destination dir>"
  exit 1
fi

REPO_URL="$1"
DEST_DIR="$2"

echo "🔄 Clonage du dépôt depuis : $REPO_URL"
git clone "$REPO_URL" "$DEST_DIR"

cd "$DEST_DIR"

echo "📦 Installation des dépendances via Composer"
composer install --no-interaction

echo "⚙️ Vérification des permissions sur var/cache et var/log"
mkdir -p var/cache var/log
chmod -R 0775 var/cache var/log || true

echo "✅ Clonage et installation terminés."
echo "➡️ Pour connaître la version du framework :"
echo "   cd $DEST_DIR && php bin/console --version"
