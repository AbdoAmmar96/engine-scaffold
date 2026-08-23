# BP Engine — دليل العمل داخل المشروع

منصّة مواقع عقارية (Real Estate / "Reel Estate Template") — عربي أولًا + RTL، ثنائية اللغة `/ar` `/en`،
مع Theme Engine بيتحكم فيه من الداشبورد من غير build.

© شركة شريك الأعمال لتقنية المعلومات — bp-eg.com

---

## ⚠️ أهم حاجة: الريبو ده طبقتين

```
engine-scaffold/          ← الريبو ده (السكافولد)
├── setup.sh              ← بينشئ Laravel 12 نضيف + يثبت الحزم + يولّد الموديولات + ينسخ overlay/ فوقه
├── overlay/              ← الملفات المخصّصة اللي بتتنسخ فوق Laravel النضيف
└── engine/               ← التطبيق المتولّد الفعلي — **مُتجاهَل في .gitignore وله ريبو منفصل**
```

**القاعدة:** الكود الحي بيتعدّل في `engine/`، بس أي تعديل دائم لازم **يتعمله mirror في `overlay/`**
وإلا هيضيع أول ما حد يعيد تشغيل `setup.sh`. لو عدّلت ملف موجود في `overlay/`، عدّل النسختين.
قبل ما تسلّم شغل، اتأكد إن الاتنين متطابقين للملفات اللي لمستها.

---

## الـ Stack (الإصدارات المتحقَّق منها فعليًا على الجهاز ده)

| | |
|---|---|
| PHP | 8.3.6 |
| Laravel | 12.66.0 |
| Node / npm | 24.11.0 / 11.6.1 |
| Inertia | v2 (`inertiajs/inertia-laravel` ^2.0 + `@inertiajs/react` ^2.3) |
| React | 19 + TypeScript 7 |
| Vite | 7 · Tailwind CSS | v4 (`@tailwindcss/vite`) |
| Modular MVC | `nwidart/laravel-modules` ^13 + `wikimedia/composer-merge-plugin` |
| DB | SQLite افتراضيًا (`database/database.sqlite`) — قابلة للتحويل لـ MySQL من `.env` |

حزم Spatie المركّبة: `laravel-permission` ^8، `laravel-medialibrary` ^11، `laravel-translatable` ^6.

**لا تفترض إصدار أي حزمة** — اتأكد بـ `composer show <vendor/package>` أو من `package.json`.

---

## الموديولات

`Modules/` فيها 11 موديول، كلها enabled في `modules_statuses.json`:

`Core` · `Properties` · `Compounds` · `Developers` · `Locations` · `Leads` · `Blog` · `Seo` · `Marketing` · `Pages` · `Reviews`

- **`Core`** — auth · dashboard · settings · users · media · menus · **سجل النشاط**.
- **`Properties`** — الأنضج: صفحات عامة · CRUD أدمن · «أضف عقارك» · «وحداتي» · **البحث المحفوظ + تنبيهاته** · شوهدت مؤخرًا.
- **`Seo`** — sitemap · robots · **صفحات الهبوط البرمجية** (`LandingPage` + أمر `seo:landing-pages`).
- **`Marketing`** — **المساحات الإعلانية المجدولة** (`FeaturedAd`) + **شاشة التقارير**.
- **`Pages`** و **`Reviews`** لسه stubs متولّدة.

الـ CRUD الأدمن كله بيرث من `app/Support/ResourceController.php` — أي تعديل هناك بيمسّ كل الموديولات.

---

## الراوتات (المصدر: `php artisan route:list --except-vendor` — 168 راوت)

- **الموقع العام:** `/{locale}` وتحته `/properties` (+ `/commercial` `/residential` + `{slug}` للوحدة
  **وللـ landing page** — راجع `SharedSlugSpace`) · `/compounds` · `/developers` · `/areas` · `/blog`
  · `/add-property` · `/ads/{id}` (تتبّع الضغطة) · `/recently-viewed` (JSON) · `/about` · `/contact`.
- **الحساب:** `/{locale}/account` · `/account/my-properties` · `/account/saved-searches`
  · `/account/favorites` · `/account/requests` · دخول/تسجيل/نسيت كلمة المرور.
- **الداشبورد:** `/admin` · `/admin/settings/{group}` · موارد `users` `properties` `compounds`
  `developers` `locations` `leads` `posts` `landing-pages` `featured-ads` · `/admin/media`
  · `/admin/menus` · `/admin/reports` · `/admin/activity`.
- **API:** `/api/v1/*` لكل موديول (RESTful).

### 🔴 عطل مؤكَّد في طبقة الـ API
راوتات `api/v1/*` كلها ورا middleware `auth:sanctum`، لكن **`laravel/sanctum` مش مركّب**
و `config('auth.guards')` بيرجّع `["web"]` بس. يعني كل الـ 45+ endpoint بترمي
`InvalidArgumentException: Auth guard [sanctum] is not defined` وقت التشغيل.
لو هتشتغل على الـ API: يا إما `composer require laravel/sanctum` وتظبط الجارد، يا إما تغيّر الـ middleware.

### ملاحظة تانية
راوتات `/pages` `/reviews` `/seos` (سكافولد nwidart الافتراضي) ورا `web,auth,verified` —
والـ `verified` محتاج email verification مش مركّب، فعمليًا مقفولة.

---

## Theme Engine — إزاي بيشتغل

1. الإعدادات متخزّنة في جدول `settings` (group + key)، والقراءة عن طريق
   `Modules/Core/app/Services/SettingsService.php` بكاش `rememberForever` بيتفلش لوحده عند الحفظ.
2. `resources/views/app.blade.php` بيلف على مجموعة `theme` ويحقنها كـ CSS custom properties على `:root`،
   مع تحويل `_` لـ `-` — يعني المفتاح `primary_fg` بيبقى المتغير `--primary-fg`.
3. `resources/css/app.css` بيستهلكها عن طريق Tailwind v4 `@theme inline`.

**النتيجة:** تغيير أي لون من الداشبورد بيغيّر الموقع كله من غير build. لو ضفت مفتاح ثيم جديد،
هو بيتحوّل لـ CSS var تلقائيًا — مش محتاج تعدّل الـ blade.

`<html lang>` و `dir` بيتحدّدوا من اللوكال في `app.blade.php` — العربي RTL والإنجليزي LTR.

---

## الأوامر

```bash
cd engine

composer run dev        # server + queue + pail + vite مع بعض (concurrently)
composer run test       # اختبارات PHPUnit
composer run lint       # Pint — يصلّح الفورمات
composer run lint:check # Pint — فحص من غير تعديل
composer run analyse    # PHPStan/Larastan level 5
composer run types      # tsc --noEmit
composer run check      # الأربعة مع بعض ← شغّلها قبل ما تسلّم

npm run dev             # Vite بس
npm run build           # بناء الإنتاج
npm run e2e             # Playwright smoke (بيشغّل artisan serve لوحده)
npm run e2e:ui          # Playwright بواجهة
```

### حالة الجودة الحالية (خط الأساس — متحقَّق منه)
- `tsc --noEmit` → **نضيف ✅**
- `php artisan test` → **187/187 ناجحة ✅** (13 ملف Feature — تغطية حقيقية للدومين)
- `playwright test` → **85 ناجحة + 1 متخطّى عن قصد ✅**
- `phpstan level 5` → **58 خطأ** ❌ (خط أساس، مش هدف — **متزوّدش عليه**)
- `pint --test` → ملفات قديمة محتاجة فورمات ❌ (الجديد كله نضيف — سيب القديم لكوميت لوحده)

لو الشغل بتاعك مالوش علاقة بيهم، متصلّحهمش في نفس الـ commit — اعملهم لوحدهم.

---

## أدوات مركّبة عشان تشتغل بأقصى كفاءة

- **Laravel Boost MCP** (`laravel/boost`) — مسجّل في `.mcp.json` على مستوى الجذر.
  الأدوات المتاحة فعليًا: `application-info` · `browser-logs` · `database-connections` ·
  `database-query` · `database-schema` · `get-absolute-url` · `last-error` · `read-log-entries` ·
  `record-rule` · `search-docs`.
  **استخدم `search-docs` بدل ما تفتكر الـ API** — بيرجّع توثيق نسخة Laravel 12 المركّبة بالظبط.
- **Boost skills** — مربوطة في `.claude/skills/` (symlinks لـ `engine/.claude/skills/`):
  `laravel-best-practices` · `inertia-react-development` · `tailwindcss-development` · `infer-conventions`.
  فعّلها لما تشتغل في مجالها من غير ما تستنى.
- **Larastan** (`phpstan.neon`, level 5) · **Pint** · **Playwright** (`playwright.config.ts`, `tests/e2e/`).
- CLI: `rg` · `fd` · `jq` · `sqlite3` · `gh`.
- بعد `php artisan boost:update` مش محتاج تعمل حاجة — الـ skills symlinks بتتحدّث لوحدها.

---

## أنظمة فرعية لازم تعرفها قبل ما تلمس حاجة

- **دورة الاعتماد.** الوحدة مبتظهرش غير بـ `status = published` **و** `is_active = true` — استخدم
  `Property::published()` في أي استعلام عام. التحكم في `PropertyAdminController::applyModeration`
  بتلات طبقات: `publish listings` بيحدد الحالة · `manage catalog` لوحدها بتعدّل من غير ما تلمس
  الحالة · صاحب الوحدة أي تعديل منه بيرجّعها للمراجعة.
- **مساحة الاسم المشتركة للروابط.** `/properties/{slug}` بيخدم الوحدات **وصفحات الهبوط**.
  التفرّد مفروض على الجدولين في `app/Support/SharedSlugSpace.php` — أي موديل جديد ينشر تحت
  نفس المسار لازم يضاف هناك.
- **صفحات الهبوط.** بتتولّد بأمر `seo:landing-pages` من الوحدات الموجودة فعلًا. النصوص الفاضية
  بتتولّد، والمكتوبة بتغلب. الأمر idempotent وبيتشغّل في كل `db:seed` وأسبوعيًا من الجدولة.
- **العدّادات.** المشاهدات والظهور والضغطات بتتزوّد على **query builder** مش على الموديل —
  عشان `updated_at` ماتتحركش (خريطة الموقع بتبني `lastmod` عليها) وسجل النشاط ما يتلوّثش.
- **سجل النشاط.** `App\Support\LogsActivity` متركّب على موديلات الدومين. بيسجّل أفعال الناس بس
  (اللي فيها مستخدم مسجّل) — السيدرز والأوامر مبتتسجّلش عن قصد.
- **البريد.** `MAIL_MAILER=sendmail` على الاستضافة المشتركة. استعادة كلمة المرور وتنبيهات
  البحث المحفوظ معتمدين عليه — لو رجع `log` الرسايل بتروح في الفراغ من غير خطأ.
- **الجدولة** في `routes/console.php`: `searches:alert` يوميًا 9ص · `seo:landing-pages` أسبوعيًا.
  محتاجة cron على السيرفر (`php artisan schedule:run` كل دقيقة) — **لسه مش مركّبة على الإنتاج**.

## أعراف الكود

- اتبع أعراف الملفات المجاورة — بص على sibling files قبل ما تنشئ ملف جديد.
- الواجهة عربية أولًا: أي نص جديد لازم يكون له نسخة `ar` و `en`، وأي layout لازم يشتغل RTL صح
  (استخدم logical properties: `ms-`/`me-`/`ps-`/`pe-` بدل `ml-`/`mr-`).
- الألوان من توكنز الثيم — **بلاش hex ثابت في JSX/CSS**، وإلا الـ Theme Engine بيتكسر.
- كومبوننتات مشتركة موجودة في `resources/js/Components/site/` — دوّر قبل ما تكتب واحدة جديدة.
- الأدمن بيستخدم `Components/admin/ui.tsx` (Form Kit) و `ResourceTable.tsx` (Table Kit).
- بيانات تجريبية في `app/Support/DemoContent.php` — دي مؤقتة لحد ما الموديلات الحقيقية تحل محلها.

---

## توثيق المشروع

- `docs/PDR.md` — مراجعة تصميم/متطلبات المشروع: جرد الموديولات، مراجعة الـ frontend، الفجوات الحرجة،
  الديون التقنية، خارطة الطريق.
- `docs/SAAS-READINESS.md` — تحليل التحوّل لـ SaaS: العوائق في الكود، المسار الموصى به، خطة الهجرة، التقديرات.
- `README.md` — التشغيل وسجل الإصدارات (v1.1، v1.2).
- `engine/CLAUDE.md` — إرشادات Laravel Boost (متولّدة تلقائيًا — متعدّلهاش يدويًا).
