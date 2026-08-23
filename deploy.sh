#!/usr/bin/env bash
# ============================================================
#  deploy.sh — رفع الإنجن على استضافة Hostinger مشتركة
#
#  Hostinger بتعمل الساب دومين كمجلد جوه الدومين الأب، مش كمجلد مستقل،
#  فالمسارات بتتحدد من بره:
#    WEB_ROOT ← اللي النت بيشوفه (محتويات public/ بتاعة لارافيل)
#    APP_DIR  ← التطبيق، لازم يكون بره الويب روت
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

# مسارات نسبية للـ home على السيرفر
REMOTE_WEB="${REMOTE_WEB:-domains/alkarma-egy.com/public_html/example}"
REMOTE_APP="${REMOTE_APP:-bp-engine}"

ssh_run() { ssh -i "$SSH_KEY" -p "$SSH_PORT" "$SSH_HOST" "$@"; }
push()    { rsync -az --delete -e "ssh -i $SSH_KEY -p $SSH_PORT" "$@"; }

echo "==> [1/7] فحص الويب روت"
ssh_run "[ -d ~/$REMOTE_WEB ]" || {
    echo ""
    echo "❌ مفيش ~/$REMOTE_WEB على السيرفر."
    echo "   اعمل الساب دومين من hPanel، أو حدّد المسار الصح:"
    echo "   REMOTE_WEB=domains/<الدومين>/public_html/<المجلد> ./deploy.sh"
    exit 1
}

# التطبيق لازم يفضل بره الويب روت، وإلا أي حد يقدر يفتح .env
case "$REMOTE_APP" in
    */public_html/*|*/public_html) echo "❌ APP جوه الويب روت — ده بيعرّض .env للنت"; exit 1 ;;
esac

echo "==> [2/7] بناء الأصول محليًا (السيرفر مفيهوش node)"
( cd "$APP" && npm run build )

echo "==> [3/7] رفع كود التطبيق"
# نبضة الجدولة بتتكتب على السيرفر نفسه — لو رفعنا نسخة الجهاز المحلي
# فوقها، كرون واقف هيبان شغّال وده أسوأ من مفيش مؤشّر خالص.
# و ACCOUNTS.txt عايش على السيرفر بس (مش في git) — من غير الاستثناء
# ده الـ --delete كان بيمسحه في كل رفعة، وده اللي حصل فعلًا.
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
  --exclude='storage/app/schedule-heartbeat' \
  --exclude='ACCOUNTS.txt' \
  --exclude='database/*.sqlite' \
  --exclude='vendor' \
  --exclude='tests' \
  "$APP/" "$SSH_HOST:$REMOTE_APP/"

echo "==> [4/7] رفع الملفات العامة"
# --delete مع استثناء storage: بينضّف بقايا أي نشر قديم
# من غير ما يمسح لينك الميديا
rsync -az --delete --exclude='storage' -e "ssh -i $SSH_KEY -p $SSH_PORT" \
  --exclude='hot' \
  "$APP/public/" "$SSH_HOST:$REMOTE_WEB/"

echo "==> [5/7] توجيه index.php للتطبيق"
# المسار المطلق ضروري: الساب دومين بيبقى متداخل جوه public_html بتاع
# الدومين الأب، فـ dirname(__DIR__) بيطلّع مجلد غلط.
# بنكتب قالب فيه علامة وبنبدّلها على السيرفر — أنضف من الهروب في heredoc.
ssh_run "cat > ~/$REMOTE_WEB/index.php" <<'PHP'
<?php

// التطبيق بره الويب روت — ده نمط الاستضافة المشتركة:
// المجلد ده هو الوحيد اللي بيتشاف من النت.
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$laravelPath = '__APP_PATH__';

if (file_exists($maintenance = $laravelPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelPath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $laravelPath.'/bootstrap/app.php';

// الويب روت مش جوه مجلد التطبيق في النمط ده، فلازم نقوله هو فين —
// من غير كده Vite بيدوّر على manifest.json في المكان الغلط و asset() بتطلع مسارات غلط
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
PHP

ssh_run "sed -i \"s|__APP_PATH__|\$HOME/$REMOTE_APP|\" ~/$REMOTE_WEB/index.php"
ssh_run "grep -q '__APP_PATH__' ~/$REMOTE_WEB/index.php && { echo '❌ المسار مااتبدلش'; exit 1; } || true"

echo "==> [6/7] تجهيز التطبيق على السيرفر"
ssh_run bash -s <<REMOTE
set -euo pipefail
cd ~/$REMOTE_APP

# الحزم الأول — أي أمر artisan محتاج vendor/autoload.php
composer install --no-dev --optimize-autoloader --no-interaction --quiet

touch database/database.sqlite

# .env بيتعمل مرة واحدة بس — إعادة الرفع مبتلمسوش
if [ ! -f .env ]; then
    cp .env.example .env
    sed -i "s|^APP_ENV=.*|APP_ENV=production|"          .env
    sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|"           .env
    sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|"     .env
    sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=file|"  .env
    sed -i "s|^CACHE_STORE=.*|CACHE_STORE=file|"        .env

    # الاستضافة المشتركة فيها sendmail جاهز — من غيره «نسيت كلمة المرور»
    # بيكتب الرسالة في اللوج والعميل مبيوصلوش حاجة
    sed -i "s|^MAIL_MAILER=.*|MAIL_MAILER=sendmail|"                        .env
    sed -i "s|^MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=\"no-reply@$DOMAIN\"|" .env
    echo "   .env اتعمل"
fi

# المفتاح بيتولّد لو ناقص — مش جوه شرط إنشاء .env.
# لو التشغيلة الأولى وقعت بعد ما عملت .env، الشرط ده كان بيتخطّى التوليد للأبد.
grep -qE '^APP_KEY=base64:' .env || php artisan key:generate --force

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
