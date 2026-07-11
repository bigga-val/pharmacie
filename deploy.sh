#!/bin/bash

set -e

echo "==> Déploiement en cours..."

echo "==> Pull du code..."
git pull origin main

echo "==> Installation des dépendances PHP..."
composer install --no-dev --optimize-autoloader

echo "==> Installation des dépendances Node..."
npm install --production

echo "==> Build Tailwind CSS..."
npm run build

echo "==> Migrations base de données..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "==> Nettoyage du cache..."
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

echo "==> Déploiement terminé avec succès !"
