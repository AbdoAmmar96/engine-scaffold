#!/usr/bin/env bash
# ============================================================
#  sync-overlay.sh — يحدّث overlay/ من engine/
#
#  overlay هو اللي setup.sh بينسخه فوق مشروع لارافيل جديد.
#  أي ملف اتعدّل في engine/ ومترحّلش هنا معناه إن setup.sh
#  بيطلّع نسخة أقدم من الموقع — وده اللي حصل فعلًا وخلّى
#  النسختين يتفصلوا. شغّل السكربت ده بعد أي شغل في engine/.
#
#  © Business Partner for Information Technology — bp-eg.com
# ============================================================
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENGINE="${1:-$ROOT/engine}"
OVERLAY="$ROOT/overlay"

[ -d "$ENGINE" ] || { echo "مفيش مجلد engine في: $ENGINE"; exit 1; }

echo "==> بننضّف overlay القديم"
rm -rf "$OVERLAY"
mkdir -p "$OVERLAY"

# المسارات اللي الإنجن بيضيفها أو بيعدّلها فوق لارافيل الافتراضي.
# مش بننسخ: vendor · node_modules · .env · database.sqlite · storage
# · public/build · public/hot · composer.json · package.json
#   (الاتنين دول setup.sh بيبنيهم بأوامر التثبيت)
PATHS=(
    "app"
    "bootstrap/app.php"
    "database/seeders"
    "lang"
    "resources/css"
    "resources/js"
    "resources/views/app.blade.php"
    "routes/web.php"
    # اختبارات الأدوار والصلاحيات جزء من المنتج مش من بيئة التطوير
    "tests/Feature"
    "tsconfig.json"
    "vite.config.ts"
    "public/images"
    "public/videos"
)

# ملفات الموديولات: الكود والراوتات والميجريشنز والسيدرز بس،
# باقي السكافولد بيتولّد من module:make
MODULE_PATHS=(
    "app"
    "routes"
    "database/migrations"
    "database/seeders"
    "resources/views"
)

copy() {
    local rel="$1"
    local src="$ENGINE/$rel"

    [ -e "$src" ] || return 0

    mkdir -p "$OVERLAY/$(dirname "$rel")"
    cp -R "$src" "$OVERLAY/$rel"
}

echo "==> بننسخ ملفات التطبيق"
for p in "${PATHS[@]}"; do copy "$p"; done

echo "==> بننسخ الموديولات"
for module_dir in "$ENGINE"/Modules/*/; do
    module="$(basename "$module_dir")"

    for p in "${MODULE_PATHS[@]}"; do copy "Modules/$module/$p"; done
done

echo "==> بننضّف اللي متولّد"
find "$OVERLAY" -name ".DS_Store" -delete
rm -rf "$OVERLAY/public/storage"

files=$(find "$OVERLAY" -type f | wc -l)
size=$(du -sh "$OVERLAY" | cut -f1)

echo ""
echo "✅ overlay اتحدّث: $files ملف · $size"
echo "   راجع الفرق: git -C \"$ROOT\" status --short overlay"
