#!/usr/bin/env bash
# ============================================================
#  BP Engine — Phase 1 Scaffold
#  Laravel 12 + Inertia v2 + React TS + Vite + Tailwind v4
#  Modular MVC (nwidart) + Theme Engine + Custom Admin (no Filament)
#  © Business Partner for Information Technology — bp-eg.com
# ============================================================
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="${1:-engine}"

echo "==> [0/7] فحص المتطلبات"
command -v php >/dev/null      || { echo "PHP 8.3+ مطلوب"; exit 1; }
command -v composer >/dev/null || { echo "Composer مطلوب"; exit 1; }
command -v node >/dev/null     || { echo "Node 20+ مطلوب"; exit 1; }

echo "==> [1/7] إنشاء مشروع Laravel 12 في: $PROJECT_DIR"
composer create-project laravel/laravel:^12.0 "$PROJECT_DIR" --no-interaction
cd "$PROJECT_DIR"

echo "==> [2/7] تثبيت حزم الباك إند"
# nwidart/laravel-modules بيجيب composer-merge-plugin كـ dependency،
# فلازم نسمح بيه قبل التثبيت مش بعده وإلا الـ require بيفشل
composer config --no-plugins allow-plugins.wikimedia/composer-merge-plugin true

composer require inertiajs/inertia-laravel:^2.0 \
  nwidart/laravel-modules \
  spatie/laravel-permission \
  spatie/laravel-medialibrary \
  spatie/laravel-translatable \
  --no-interaction

php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --no-interaction
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations" --no-interaction

echo "==> [3/7] توليد الموديولات العشرة"
# دمج composer.json الخاص بكل موديول (merge-plugin) — لازم قبل التوليد،
# من غيره كلاسات الموديولات مش بتدخل الـ autoload
composer require wikimedia/composer-merge-plugin --no-interaction
php -r '
  $f = "composer.json";
  $j = json_decode(file_get_contents($f), true);
  $j["extra"]["merge-plugin"]["include"] = ["Modules/*/composer.json"];
  file_put_contents($f, json_encode($j, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
'

# كل الموديولات في أمر artisan واحد — لو اتولدوا واحد واحد، تاني أمر بيفشل
# لأن الـ ServiceProvider بتاع الموديول الأول لسه مش في الـ autoload
php artisan module:make Core Pages Locations Developers Compounds Properties Leads Blog Seo Reviews --no-interaction

composer dump-autoload

echo "==> [4/7] تثبيت حزم الفرونت إند"
# الكلاينت لازم يطابق inertia-laravel v2: الـ 3.x بيقرا الـ page من
# <script data-page> بينما توجيه @inertia بتاع v2 بيطلع <div id="app" data-page>
npm install react react-dom @inertiajs/react@^2.0 lucide-react
# plugin-react متثبّتة على ^5.2 — الـ 6.x بتطلب vite ^8 و Laravel 12 شايل vite ^7
npm install -D typescript @types/react @types/react-dom @vitejs/plugin-react@^5.2.0 tailwindcss @tailwindcss/vite

# طبقة الجودة: من غيرها الـ overlay بيجيب playwright.config.ts و phpstan.neon
# وسويت الاختبارات من غير الحزم اللي بتشغّلهم.
npm install -D @playwright/test
composer require --dev larastan/larastan laravel/boost --no-interaction

echo "==> [5/7] تركيب ملفات الإنجن (overlay)"
cp -Rf "$SCRIPT_DIR/overlay/." .
rm -f vite.config.js   # نسختنا vite.config.ts هي المعتمدة

echo "==> [6/7] قاعدة البيانات (SQLite افتراضيًا — بدّلها لـ MySQL من .env وقت ما تحب)"
touch database/database.sqlite
php artisan migrate --force
php artisan module:migrate --force || true
php artisan db:seed --force
php artisan storage:link || true

echo "==> [7/7] تم ✅"
echo ""
echo "  شغّل المشروع:      cd $PROJECT_DIR && composer run dev"
echo "  الموقع:            http://localhost:8000/ar"
echo "  الداشبورد:         http://localhost:8000/admin"
echo "  الدخول:            admin@bp-eg.com  /  password   (غيّرها فورًا)"
echo ""
echo "  جرّب قوة الـ Theme Engine: من الداشبورد → الإعدادات → الهوية والألوان،"
echo "  غيّر اللون الأساسي واعمل ريفريش للموقع — من غير أي build."
