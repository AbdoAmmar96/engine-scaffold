#!/usr/bin/env bash
# ============================================================
#  deploy.sh — رفع الإنجن على استضافة Hostinger مشتركة
#
#  النمط اللي الحساب ماشي عليه:
#    ~/domains/<domain>/laravel/      ← التطبيق (بره الويب روت)
#    ~/domains/<domain>/public_html/  ← محتويات public/ بتاعة لارافيل
#
#  الأصول بتتبني هنا لأن السيرفر مفيهوش node.
#  بيحافظ على: .env · database/*.sqlite · storage/app · public_html/storage
#
#  © Business Partner for Information Technology — bp-eg.com
# ============================================================
set -euo pipefail

DOMAIN="${DOMAIN:-example.alkarma-egy.com}"
SSH_HOST="${SSH_HOST:-u188133440@82.25.102.203}"
SSH_PORT="${SSH_PORT:-65002}"
SSH_KEY="${SSH_KEY:-$HOME/.ssh/hostinger_alkarma}"

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/engine"

REMOTE_BASE="domains/$DOMAIN"
REMOTE_APP="$REMOTE_BASE/laravel"
REMOTE_WEB="$REMOTE_BASE/public_html"

ssh_run() { ssh -i "$SSH_KEY" -p "$SSH_PORT" "$SSH_HOST" "$@"; }
push()    { rsync -az --delete -e "ssh -i $SSH_KEY -p $SSH_PORT" "$@"; }

echo "==> [1/7] فحص إن الساب دومين متعمل"
ssh_run "[ -d ~/$REMOTE_WEB ]" || {
    echo ""
    echo "❌ مفيش ~/$REMOTE_WEB على السيرفر."
    echo "   اعمل الساب دومين الأول من hPanel:"
    echo "   Websites → alkarma-egy.com → Subdomains → أضف: ${DOMAIN%%.*}"
    exit 1
}

echo "==> [2/7] بناء الأصول محليًا (السيرفر مفيهوش node)"
( cd "$APP" && npm run build )

echo "==> [3/7] رفع كود التطبيق"
push \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='.env' \
  --exclude='public' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='storage/app/public/*' \
  --exclude='database/*.sqlite' \
  --exclude='vendor' \
  --exclude='tests' \
  "$APP/" "$SSH_HOST:$REMOTE_APP/"

echo "==> [4/7] رفع الملفات العامة"
# --delete متشالة هنا عن قصد: public_html فيه storage/ (ميديا المستخدم)
rsync -az -e "ssh -i $SSH_KEY -p $SSH_PORT" \
  --exclude='storage' \
  --exclude='hot' \
  "$APP/public/" "$SSH_HOST:$REMOTE_WEB/"

echo "==> [5/7] توجيه index.php للتطبيق"
ssh_run "cat > ~/$REMOTE_WEB/index.php" <<'PHP'
<?php

// التطبيق بره الويب روت — ده نمط الاستضافة المشتركة:
// public_html هو الوحيد اللي بيتشاف من النت.
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$laravelPath = dirname(__DIR__).'/laravel';

if (file_exists($maintenance = $laravelPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelPath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $laravelPath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
PHP

echo "==> [6/7] تجهيز التطبيق على السيرفر"
ssh_run bash -s <<REMOTE
set -euo pipefail
cd ~/$REMOTE_APP

# .env بيتعمل مرة واحدة بس — إعادة الرفع مبتلمسوش
if [ ! -f .env ]; then
    cp .env.example .env
    sed -i "s|^APP_ENV=.*|APP_ENV=production|"          .env
    sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|"           .env
    sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|"     .env
    sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=file|"  .env
    sed -i "s|^CACHE_STORE=.*|CACHE_STORE=file|"        .env
    php artisan key:generate --force
    echo "   .env اتعمل"
fi

touch database/database.sqlite

composer install --no-dev --optimize-autoloader --no-interaction --quiet

php artisan migrate --force
php artisan db:seed --force || true

mkdir -p storage/app/public
chmod -R 775 storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache
REMOTE

echo "==> [7/7] ربط مجلد الميديا"
# storage:link بيعمل اللينك جوه public/ اللي مش موجود هنا — بنعمله يدوي
ssh_run "rm -rf ~/$REMOTE_WEB/storage && ln -sfn ~/$REMOTE_APP/storage/app/public ~/$REMOTE_WEB/storage"

echo ""
echo "✅ اترفع: https://$DOMAIN"
echo "   اللوحة: https://$DOMAIN/admin"
