# تقييم جاهزية BP Engine للتحوّل لمنتج SaaS متعدد المستأجرين

> تحليل مبني على قراءة الكود الفعلي في `engine/` — مش على افتراضات Laravel الافتراضية.
> تاريخ التقييم: 2026-08-20 · الفرع: `main` · آخر كوميت في الإنجن: `f18c6ed`
> حجم الكود المفحوص: **4,763 سطر PHP** (`app/` + `Modules/`) · **3,665 سطر TS/TSX** (`resources/js/`)

---

## 1. الحكم النهائي

**آه، المشروع قابل يبقى SaaS — والكود الحالي مش محتاج rewrite، محتاج adaptation.** السبب إن الـ 4,763 سطر PHP دول **مفيهمش ولا سطر واحد بيفترض tenant**، ومفيش أي كويري cross-model معقّدة، ومفيش أي state متسرّب غير الكاش — يعني الكود "نضيف" بمعنى إنه فاضي من التعقيد اللي بيصعّب الهجرة، مش بمعنى إنه جاهز.

**بس اللي ناقص مش الـ multi-tenancy — الناقص هو المنتج نفسه.** الـ tenancy تقريبًا **22 يوم مطور**؛ إن حواليها (provisioning، billing، custom domains، super admin، backups، observability، tests، CI) تقريبًا **125 يوم إضافي**. الإجمالي الواقعي **145–175 يوم مطور ≈ 7–8 شهور لمطور واحد** أو **4–4.5 شهر لفريق اتنين**. أي حد بيقولك "أسبوعين وهنعمل tenant_id" بيوصف المرحلة الأولى بس من سبعة.

---

## 2. تقييم الجاهزية — جدول المحاور

الجهد بأيام مطور سينيور واحد، شامل الاختبار والمراجعة، **مش** شامل الـ buffer.

| المحور | الحالة الحالية | المطلوب لـ SaaS | الفجوة | الجهد (يوم) |
|---|---|---|---|---|
| **Tenancy (العمود الفقري)** | صفر. مفيش جدول `tenants`، مفيش middleware، مفيش `Route::domain()` في أي ملف. الأبليكيشن بيفترض إنه **بيخدم عميل واحد** من أول `bootstrap/app.php` لآخر موديل | تحديد الـ tenant من الـ Host + عزل كامل للبيانات + bootstrappers للكاش والسيشن والستوريدج | **كاملة** — بس الفجوة "فاضية" مش "متشابكة" | **22** |
| **Auth & Roles** | `session` guard واحد + `role:admin` نصّي. `users.email` **unique عالميًا**. spatie/permission بـ `teams => false` وكاش مفتاحه ثابت | مستخدمين معزولين لكل tenant + guard منفصل للـ super admin + دعوات + reset password | **كبيرة** — و`users.email` unique هو أول حاجة هتقف قدامك | **10** |
| **Data isolation** | 8 موديلات Eloquent، **ولا واحد فيهم فيه Global Scope**. `ResourceController` بيعمل `findOrFail($id)` من غير أي scope — ده IDOR جاهز في shared DB | عزل بالبناء (structural)، مش بالانضباط (disciplinary) | **كاملة**، والخطر تراكمي مع كل كويري جديدة | **6** (لو DB-per-tenant) / **20+** (لو row-level) |
| **Theming / Branding** | **أقوى نقطة في المشروع.** الثيم بيتقرا من DB كل ريكوست ويتحقن كـ CSS vars — مفيش أي حاجة مخبوزة وقت الـ build | نفس الميكانيزم + رفع لوجو + خطوط + assets معزولة لكل tenant | **صغيرة** — الأساس شغال فعلًا | **6** |
| **Media** | **مش موجودة أصلًا.** `spatie/laravel-medialibrary` متثبّتة والمايجريشن اتعمل، بس **ولا موديل واحد بيستخدم `InteractsWithMedia`**. الصور كلها أعمدة `string` بمسارات نصّية | uploader حقيقي + disk لكل tenant + conversions + CDN | **كاملة (build from scratch، مش migration)** | **12** |
| **Billing & Plans** | صفر. مفيش cashier، مفيش plans، مفيش subscriptions، مفيش feature gating | خطط + اشتراكات + بوابة دفع مصرية + فواتير + dunning | **كاملة** | **28** |
| **Custom domains + SSL** | صفر. `config/session.php:159` بيقرا `SESSION_DOMAIN` من `.env` (قيمة واحدة)، ومفيش domain model | جدول domains + تحقق DNS + ACME/Let's Encrypt أوتوماتيك | **كاملة** | **12** |
| **Ops (backup/monitor/CI)** | صفر تمامًا. مفيش `.github/`، مفيش Docker، مفيش Sentry، اللوج `stack → single` ملف واحد، **واختبارين stub بس** في `tests/` | CI + staging + backups لكل tenant + error tracking + APM + alerting | **كاملة** | **25** |
| **SEO per tenant** | `public/robots.txt` ملف ثابت واحد لكل الدومينات. موديول `Seo` **كنترولر فاضي** بيرجّع `view()` من غير أي منطق. مفيش sitemap ولا canonical | robots + sitemap + canonical + hreflang لكل دومين | **كاملة** | **8** |
| **Onboarding / Provisioning** | `setup.sh` — سكريبت باش بيعمل `composer create-project` + `npm install`. دقايق وشبكة وتدخّل بشري | signup ذاتي + job provisioning + seeding + welcome | **كاملة** | **15** |
| **Comms (email/SMS/WhatsApp)** | `MAIL_MAILER=log` في `.env` — مفيش أي إيميل بيتبعت. الواتساب "تكامل" = بناء رابط `wa.me` في الفرونت | provider حقيقي + templates + SMS/OTP + WhatsApp Business API | **كاملة** | **10** |
| **Testing** | `tests/Feature/ExampleTest.php` + `tests/Unit/ExampleTest.php` — دول الاتنين اللي موجودين | suite حقيقي، وخصوصًا **اختبارات عزل الـ tenant** | **كاملة** | **15** |
| | | | **الإجمالي** | **~145–169** |

---

## 3. العوائق الحقيقية في الكود

### 3.1 🔴 قاتل — كاش الإعدادات مفتاحه عالمي (الـ Theme Engine هيتسرّب بين العملاء)

```php
// engine/Modules/Core/app/Services/SettingsService.php:18
return Cache::rememberForever(
    "settings.group.{$group}",          // ← مفيش tenant في المفتاح
    fn () => Setting::query()->where('group', $group)->pluck('value', 'key')->toArray()
);
```

- `SettingsService.php:18-24` — `settings.group.theme` مفتاح واحد لكل الأبليكيشن
- `SettingsService.php:35` — `'settings.public'` نفس المشكلة
- `SettingsService.php:68-75` — `flush()` بيمسح للكل، يعني حفظ إعدادات عميل بيبطّل كاش الكل

ودي بتوصل لكل ريكوست عبر مدخلين:
- `engine/resources/views/app.blade.php:5` → `$settings->group('theme')` → بيتحقن كـ CSS vars في `app.blade.php:22-28`
- `engine/app/Http/Middleware/HandleInertiaRequests.php:28` → `->public()` → بيتشارك كـ Inertia props لكل صفحة

**الأثر العملي:** أول عميل يفتح الموقع بيملى الكاش، وبعده **كل العملاء بيشوفوا ألوانه ولوجوه واسمه وأرقام تليفوناته**. ده مش bug بيظهر في التطوير — ده بيظهر في البرودكشن بعد أول tenant تاني.

بالإضافة لـ `engine/config/cache.php:115`:
```php
'prefix' => env('CACHE_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-cache-'),
```
البرفكس مشتق من `APP_NAME` — قيمة واحدة للأبليكيشن كلها.

### 3.2 🔴 قاتل — القيود الفريدة (unique) عالمية على 3 جداول

| الملف:السطر | القيد | ليه بيكسر SaaS |
|---|---|---|
| `engine/database/migrations/0001_01_01_000000_create_users_table.php:17` | `$table->string('email')->unique()` | نفس الشخص مش هيقدر يبقى مدير في وكالتين — وده سيناريو عادي جدًا في السوق المصري |
| `engine/Modules/Blog/database/migrations/2026_03_01_000002_create_posts_table.php:15` | `$table->string('slug')->unique()` | وكالتين مش هيقدروا يكتبوا مقال `/blog/دليل-التجمع-الخامس` |
| `engine/Modules/Core/database/migrations/2026_01_01_000001_create_settings_table.php:17` | `$table->unique(['group', 'key'])` | **صف واحد بس في الدنيا للـ `theme.primary`** — يعني لون أساسي واحد لكل العملاء |

الأخير هو ألطف مثال على المشكلة: **جدول الإعدادات نفسه — قلب الـ Theme Engine — مصمّم هيكليًا لعميل واحد.**

كمان `Modules/Blog/app/Models/Post.php:40`:
```php
while (static::where('slug', $slug)->when(...)->exists()) {
```
مولّد الـ slug بيدوّر في الجدول كله — في shared DB ده بيخلّي slugs العميل A تأثر على العميل B.

### 3.3 🔴 قاتل — CRUD عام بدون أي scope (IDOR جاهز)

`engine/app/Support/ResourceController.php` هو الكلاس اللي **كل شاشات الأدمن** بترثه (Properties, Compounds, Developers, Locations, Leads, Blog, Users):

```php
// :80
$query = $this->modelClass()::query()->with($this->with());   // كل الصفوف في الجدول

// :115
$item = $this->modelClass()::findOrFail($id);                 // أي ID، من غير أي فلتر

// :137
$model = $this->modelClass()::findOrFail($id);                // نفس الحكاية في update

// :148-156
$model = $this->modelClass()::findOrFail($id);                // ...وفي destroy
```

في نموذج shared-DB، `GET /admin/leads/812/edit` بيدّي وكالة **الـ lead بتاع منافسها** — وفي سوق العقارات، **جدول الـ leads هو البيزنس نفسه**. الفلترة الوحيدة اللي موجودة هي `is_active` في `app/Support/Catalog.php:20,38,57`.

### 3.4 🔴 قاتل — spatie/laravel-permission شغّالة في وضع non-teams

```php
// engine/config/permission.php:38
'team' => null,

// engine/config/permission.php:151
'teams' => false,

// engine/config/permission.php:209
'key' => 'spatie.permission.cache',      // مفتاح كاش واحد للكل
```

والمايجريشن `engine/database/migrations/2026_08_16_154839_create_permission_tables.php:40-48,65-69,88-92` كله متلفّ في `if ($teams || config('permission.testing'))` — يعني **أعمدة `team_id` مش موجودة في قاعدة البيانات الحالية أصلًا**.

النتيجة: `Role::findOrCreate('admin')` في `Modules/Core/database/seeders/AdminUserSeeder.php:14` بيعمل **دور واحد مشترك بين كل الوكالات**، و`User::role('admin')` في `Modules/Core/app/Http/Controllers/UserAdminController.php:157` بيعدّ مديري **كل** الوكالات. يعني "آخر مدير" بيتحسب غلط عبر الـ tenants.

في نموذج row-level ده بيتطلب: تفعيل `teams`، مايجريشن جديد، و`PermissionsTeamResolver` مخصص. في نموذج DB-per-tenant المشكلة **بتختفي تمامًا** لأن كل قاعدة فيها أدوارها.

### 3.5 🟠 عائق — التوجيه: مفيش subdomain ولا custom domain، والمسار محجوز بالفعل

```php
// engine/routes/web.php:7-13
Route::redirect('/', '/ar');

Route::prefix('{locale}')
    ->whereIn('locale', ['ar', 'en'])
    ->middleware('locale')
    ->group(function () { ... });
```

- **`Route::domain()` مش مستخدمة ولا مرة** في `routes/` ولا `Modules/*/routes/` (بحث كامل: صفر نتيجة).
- **المسار الأول محجوز للغة.** لو فكّرت في tenancy بالمسار (`/agency-x/ar/properties`) هتضطر تلف كل الجروب، وتعدّل `app/Http/Middleware/SetLocale.php:21`:
  ```php
  URL::defaults(['locale' => $locale]);   // ← لازم يبقى ['tenant' => ..., 'locale' => ...]
  ```
  وتراجع كل استدعاء `route()` في 3,665 سطر TSX. مسار مؤلم بلا فايدة.
- **راوتات الأدمن مالهاش أي قيد دومين** — `Modules/Core/routes/web.php:16` و`Modules/Properties/routes/web.php:13` و`Modules/Leads/routes/web.php:12` كلهم `Route::prefix('admin')` عارية.
- `engine/bootstrap/app.php:14-26` — الـ middleware stack فيه `HandleInertiaRequests` + `role`/`permission`/`locale` aliases بس. **مفيش أي مكان بيتحدد فيه سياق الـ tenant.**

**الحكم:** الـ subdomain (`agency.bp-engine.com`) + custom domain هما الطريق الوحيد المعقول، وهما فعلًا **مش متعارضين مع الكود الحالي** — مجرد إضافة `Route::domain()` أو middleware بيقرا الـ Host. ده تمكين (enabler)، مش عائق. العائق الحقيقي هو إن `engine/app/Providers/AppServiceProvider.php` فاضي تمامًا (`register()` و`boot()` فيهم `//`) — مفيش أي نقطة ربط موجودة.

### 3.6 🟠 عائق — الميديا: مش موجودة، فلازم تتبني صح من أول مرة

- **مفيش `engine/config/media-library.php`** — الحزمة شغالة على الافتراضيات من `vendor/spatie/laravel-medialibrary/config/media-library.php`:
  - `:36` → `'disk_name' => env('MEDIA_DISK', 'public')`
  - `:144` → `'path_generator' => DefaultPathGenerator::class` — بيولّد `{media_id}/{filename}` — **صفر عزل بين tenants**
  - `:356` → `'prefix' => env('MEDIA_PREFIX', '')` — فاضي
- **`InteractsWithMedia` مش مستخدمة في أي ملف** في `app/` أو `Modules/` (بحث كامل: صفر نتيجة). جدول `media` موجود في `database/migrations/2026_08_16_154840_create_media_table.php` وفاضي.
- الصور دلوقتي **حقول نصّية**: `Modules/Properties/.../create_properties_table.php:25` (`image`)، `Modules/Developers/.../create_developers_table.php:17` (`logo`)، وهكذا. و`engine/resources/js/Pages/Admin/Resource/Form.tsx:110-116` بيعرض حقل النوع `image` كـ **input نصّي** بـ `placeholder="/images/demo/..."` — **مفيش أي رفع ملفات في المنتج كله.**
- `engine/config/filesystems.php:41-48` — الـ disk العام:
  ```php
  'root' => storage_path('app/public'),
  'url'  => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
  ```
  الـ URL مشتق من `APP_URL` — قيمة واحدة. مع custom domains ده معناه إن صور كل العملاء هتتقدّم من دومين واحد.
- `engine/.env` بيقول `FILESYSTEM_DISK=local` والـ S3 credentials فاضية.
- `engine/public/images/` و`engine/public/videos/` — ملفات ستاتيك متكوميتة (لوجو، فيديوهات هيرو) **مشتركة بين كل من يستخدم الكود**.

**الخبر الحلو:** لأن مفيش ميديا مبنية، مفيش data migration. **الخبر الوحش:** ده يعني ميزة أساسية في أي CMS ناقصة بالكامل، ولازمها 12 يوم.

### 3.7 🟢 نقطة قوة — الـ Theme Engine جاهز per-tenant فعلًا

دي أحسن حاجة في المشروع من منظور SaaS. الثيم **مش مخبوز وقت الـ build**:

```css
/* engine/resources/css/app.css:10-32 */
@theme inline {
    --color-primary:  var(--primary);
    --color-bg:       var(--bg);
    --font-sans:      var(--font-body), "Cairo", ui-sans-serif, system-ui, sans-serif;
    ...
}
```

`@theme inline` في Tailwind v4 بيخلّي `bg-primary` تقرا `var(--primary)` **وقت التشغيل**. والقيم بتتحقن كل ريكوست:

```blade
{{-- engine/resources/views/app.blade.php:4-9, 22-28 --}}
$theme = $settings->group('theme');
...
<style>:root { @foreach ($theme as $key => $value) --{{ str_replace('_','-',$key) }}: {{ $value }}; @endforeach }</style>
```

يعني **bundle واحد مبني مرة واحدة بيخدم N عميل بـ N هوية بصرية مختلفة، بدون rebuild ولا CSS منفصل ولا CDN لكل عميل.** ده يوفّر عليك أكبر بند تكلفة في SaaS اللي فيه theming تقيل. اللي محتاج إصلاح هو **مصدر** القيم (الكاش في 3.1) مش **آلية** الحقن.

⚠️ ملاحظة أمنية: `app.blade.php:25` بيطبع قيم الـ DB في `<style>` من غير escaping. دلوقتي القيم بيدخلها مالك الموقع، بس في SaaS دي **CSS injection** جاهزة من أي محرر عند العميل. لازم whitelist/regex على القيم قبل الحقن.

### 3.8 🟠 عائق — حالة عالمية في الكاش والسيشن والكيو

| المكوّن | الملف:السطر | القيمة | المشكلة في SaaS |
|---|---|---|---|
| Cache prefix | `engine/config/cache.php:115` | `Str::slug(env('APP_NAME')).'-cache-'` | برفكس واحد → كل مفاتيح الكاش مشتركة |
| Cache store | `engine/config/cache.php:18` + `.env` | `database` | جدول `cache` واحد مشترك — ومكلّف على N عميل |
| Session cookie | `engine/config/session.php:130-133` | `Str::slug(env('APP_NAME')).'-session'` | اسم كوكي واحد — مع subdomains على نفس الـ apex ممكن يتصادم |
| Session domain | `engine/config/session.php:159` | `env('SESSION_DOMAIN')` = `null` | `null` = per-host، وده **آمن بالصدفة**. لو حد كتب `.bp-engine.com` عشان SSO، السيشن هيمشي عبر كل الـ tenants |
| Session store | `engine/config/session.php:21` + `.env` | `database` → جدول `sessions` | `sessions.user_id` بيشاور على `users.id` — في shared DB مفيش أي حاجة تربط السيشن بالـ tenant |
| Queue | `engine/config/queue.php:16,39-42` | `database`، `'queue' => env('DB_QUEUE','default')` | **طابور واحد**. عميل بيرفع 500 صورة بيوقف الـ jobs بتاعة كل العملاء (noisy neighbour) |
| Failed jobs | `engine/config/queue.php:124-126` | جدول `failed_jobs` واحد | مفيش عزل ولا نسبة فشل لكل عميل |
| Logging | `engine/config/logging.php:21` + `.env` | `LOG_CHANNEL=stack` → `single` | **ملف لوج واحد**، مفيش tenant context، مفيش rotation |
| DB | `engine/config/database.php:20` | `env('DB_CONNECTION', 'sqlite')` | **SQLite هي الافتراضي** — مستحيلة للـ SaaS (كتابة متزامنة، مفيش N قاعدة) |

### 3.9 🟠 عائق — لا rate limiting على المدخل العام الوحيد

```php
// engine/routes/web.php:60-61
Route::post('/leads', [\Modules\Leads\Http\Controllers\LeadController::class, 'store'])
    ->name('leads.store');
```
```php
// engine/Modules/Leads/app/Http/Controllers/LeadController.php:16-37
public function store(Request $request, string $locale): RedirectResponse
{
    $data = $request->validate([...]);
    ...
    Lead::create($data);
}
```

**بحث كامل عن `throttle` و`RateLimiter` في `routes/` و`Modules/` و`app/` و`bootstrap/`: صفر نتيجة.** مفيش throttle، مفيش captcha، مفيش honeypot. المدخل العام الوحيد اللي بيكتب في الداتابيز مفتوح على الآخر. في SaaS ده بيبقى:
- تكلفة تخزين على حسابك من سبام بوتات
- تلويث بيانات العميل → شكاوى → churn
- وسيلة سهلة إن منافس يغرق فورم عميل تاني

كمان مفيش throttle على `POST /admin/login` (`Modules/Core/routes/web.php:21`) — brute force مفتوح.

### 3.10 🟡 عائق — الـ provisioning سكريبت باش تفاعلي

`engine/setup.sh` هو "آلية تركيب عميل جديد" الحالية:
- `:19` → `composer create-project laravel/laravel:^12.0` — تنزيل من الشبكة
- `:27-32` → `composer require` لخمس حزم
- `:57-59` → `npm install`
- `:62` → `cp -Rf overlay/. .`
- `:66-70` → `touch database.sqlite` + `migrate` + `db:seed`

ده **دقايق + شبكة + تدخّل بشري + PHP/Composer/Node على الجهاز**. مينفعش يكون خلف زرار "ابدأ تجربتك المجانية". لازم يتحوّل لـ Job بيعمل: صف في جدول `tenants` → قاعدة/سكيما → `tenants:migrate` → `tenants:seed` → دومين → إيميل ترحيب. المستهدف: **أقل من 30 ثانية**.

### 3.11 🟡 عائق منتجي — العلامة التجارية مخبوزة في الكود

```tsx
// engine/resources/js/Layouts/SiteLayout.tsx:248-262
{/* سطر الحقوق الإلزامي — شريك الأعمال (لا يُحذف من أي موقع خارج من الإنجن) */}
<div className="mt-10 border-t border-white/10 pt-6 text-center text-xs ...">
    <div dir="ltr">© {year} <a href="https://bp-eg.com">Business Partner for Information Technology</a>. All rights reserved.</div>
    <div dir="rtl">© {year} <a href="https://bp-eg.com">شركة شريك الأعمال لتقنية المعلومات</a>. جميع الحقوق محفوظة.</div>
</div>
```

كمان:
- `engine/resources/js/Layouts/AdminLayout.tsx:68` → `<span>إنجن شريك الأعمال</span>` + شارة `BP` ثابتة في السطر 63-65
- `engine/Modules/Core/database/seeders/AdminUserSeeder.php:18-22` → `admin@bp-eg.com` بباسورد `'password'` — **بيتزرع في كل تركيبة جديدة**. في SaaS ده بيبقى حساب خلفي معروف الإيميل والباسورد على كل tenant.
- `engine/resources/js/Layouts/SiteLayout.tsx:95,195` → fallback نصّي `"BP Engine"` لما اسم الموقع فاضي

**التبعة المنتجية (وهي الأهم):**

بتبيع دلوقتي **مشروع** لوكالة عقارية — الفوتر الإلزامي طبيعي ومقبول، ده توقيع المطوّر. بس لما تبيع **SaaS لوكالات تانية**:

1. **هيتحوّل لاعتراض بيع مباشر.** وكالة عقارية بتدفع اشتراك شهري عشان الموقع يبقى موقعها، ولوجو شركة تانية في فوتر كل صفحة بيقلّل مصداقيتها قدام عملائها.
2. **معاك ميزة monetization مجانية.** "White-label — شيل توقيع المنصة" هي **أشهر feature في الخطة الأعلى** في كل SaaS بناء مواقع (Wix، Shopify، Webflow). حوّله من "إلزامي" لـ **ميزة مدفوعة**: خطة أساسية = التوقيع موجود، خطة Pro = يتشال. ده بيحوّل عائق بيع لسبب ترقية.
3. **بس دلوقتي مستحيل تعمل كده** — لأنه **hardcoded في مكوّن React** مش قيمة في الإعدادات. لازم يتنقل لـ `settings.branding.powered_by` مربوط بـ feature flag من الخطة.
4. **فيه احتكاك تنافسي محتمل:** لو "شريك الأعمال" بتقدّم كمان خدمة مواقع عقارية بشكل مباشر، العميل بيحط اسم "منافس محتمل" على موقعه. لو المنصة هتبقى منتج مستقل، فكّر في اسم تجاري منفصل للمنصة.

### 3.12 🟡 عائق — SEO مش موجود من الأساس (بينما هو أهم قيمة في المنتج)

- `engine/public/robots.txt` — **ملف ستاتيك واحد** (`User-agent: *` / `Disallow:`) بيتقدّم لكل دومين لكل tenant
- `engine/Modules/Seo/app/Http/Controllers/SeoController.php:13-55` — **كل الميثودز stubs** بترجّع `view('seo::index')` أو فاضية. الموديول اسمه Seo وجواه صفر منطق SEO.
- `Modules/Pages` و`Modules/Reviews` نفس الحالة — `app/` فيها `Http` و`Providers` بس، **مفيش موديلات ولا مايجريشنز**
- مفيش sitemap.xml، مفيش canonical، مفيش hreflang بين `/ar` و`/en`، مفيش structured data (وهي حرجة لعقارات — `RealEstateListing` schema)
- الميتا الوحيدة: `engine/resources/views/app.blade.php:14` → `<title inertia>` بس

لوكالة عقارية، **الـ SEO هو المنتج**. لو بتبيع SaaS مواقع عقارية من غير sitemap لكل دومين، بتبيع بروشور مش قناة اكتساب.

### 3.13 🟡 عائق — الاختبارات والـ CI

- `engine/tests/` فيه **ملفين stub بس**: `Feature/ExampleTest.php` و`Unit/ExampleTest.php`
- `engine/phpunit.xml:26-27` — الاختبارات بتشتغل على `sqlite :memory:`، وده **مش هيلاقي مشاكل عزل tenants** حتى لو كتبتها
- **مفيش `.github/`** لا في `engine/` ولا في جذر الـ scaffold — صفر CI
- مفيش Docker، مفيش Procfile، مفيش أي تعريف بيئة نشر
- مفيش Pint في CI رغم إنها في `require-dev`

كتابة تعديل tenancy على قاعدة كود من غير اختبارات معناها إن أول تسريب بيانات بين عميلين هتكتشفه لما عميل يبلّغ.

---

## 4. المسار الموصى به

### ✅ الموصى به: **DB-per-tenant عبر `stancl/tenancy` v3** + قاعدة مركزية للـ control plane

المعمارية:
- **قاعدة مركزية واحدة:** `tenants`, `domains`, `plans`, `subscriptions`, `invoices`, `usage_metrics`, `super_admins`
- **قاعدة لكل tenant:** الـ 22 جدول الحاليين زي ما هم بالظبط (`settings`, `users`, `properties`, `leads`, `posts`, `media`, `sessions`, `cache`, `jobs`, ...)
- **التحديد:** subdomain (`agency.bp-engine.com`) + custom domain — الاتنين من نفس الـ `domains` table
- **الـ super admin panel** على دومين منفصل خارج سياق الـ tenancy

### ليه ده تحديدًا

**1. أقل تعديل على الكود القائم — بفرق كبير.**
الـ 4,763 سطر في `app/` و`Modules/` بتفضل **زي ما هي حرفيًا**. `Property::query()` بيرجّع عقارات الـ tenant الحالي لأن الاتصال نفسه اتبدّل. الـ 8 موديلات، الـ 8 مايجريشنز، و`ResourceController` بالكامل: **صفر تعديل**. المسار التاني (row-level) بيطلب تعديل كل موديل + كل مايجريشن + كل كنترولر + كل فهرس unique.

**2. العزل بالبناء، مش بالانضباط — وده حاسم هنا تحديدًا.**
مع **اختبارين stub** (`tests/` بيحتوي على `ExampleTest.php` مرتين) وصفر CI، الاعتماد على إن كل مطوّر مستقبلي يفتكر يحط `->where('tenant_id', ...)` هو رهان خاسر. في `ResourceController.php:115` سطر واحد اسمه `findOrFail($id)` — لو نسيت الـ scope مرة واحدة، وكالة بتشوف leads منافسها. **في العقارات، جدول الـ leads هو البيزنس نفسه.** مع DB منفصلة، النسيان ده **مستحيل فيزيائيًا**.

**3. القيود الفريدة الثلاثة بتتحل مجانًا.**
`users.email` unique (`create_users_table.php:17`)، `posts.slug` unique (`create_posts_table.php:15`)، `settings[group,key]` unique (`create_settings_table.php:17`) — كلها **تفضل صحيحة كما هي** لأن كل قاعدة معزولة. في row-level لازم تعدّل التلاتة لـ composite unique مع `tenant_id`، وتعدّل مولّد الـ slug في `Post.php:40`، وتعمل data migration.

**4. الحالة العالمية بتتحل بالـ bootstrappers الجاهزة.**
كاش `SettingsService` العالمي (`SettingsService.php:18,35`) بيتصلح بالـ `CacheTenancyBootstrapper` من غير ما تلمس السطر. نفس الحكاية للسيشن، الكيو، الفايل سيستم، والريديس. المسار التاني بيطلب namespacing يدوي في كل نقطة.

**5. spatie/permission بتشتغل زي ما هي.**
مع `teams => false` (`config/permission.php:151`) وأعمدة `team_id` **مش موجودة في الداتابيز أصلًا**، كل قاعدة بتاخد أدوارها. مفيش `PermissionsTeamResolver`، مفيش مايجريشن جديد، مفيش تعديل على `UserAdminController.php:157`.

**6. التخصيص التقيل لكل عميل هو نقطة البيع — والمعمارية دي بتدعمه.**
الدومين ده بيطلب per-client theming تقيل (والـ Theme Engine جاهز له فعلًا في `app.css:10-32` و`app.blade.php:22-28`). مع DB منفصلة تقدر تدّي عميل واحد جدول إضافي أو موديول خاص من غير ما تلوّث سكيما مشتركة بأعمدة `nullable` بتخص عميل واحد — وده اللي بيحصل حرفيًا في كل shared-schema SaaS بعد سنة.

**7. الواقع التجاري المصري: العميل هيطلب بياناته.**
بتبيع دلوقتي **template** — العميل معتاد إن الموقع "بتاعه". أول ما تتحول لـ SaaS، أول سؤال هيبقى "ولو بطّلت الاشتراك، بياناتي؟". مع DB منفصلة الرد `mysqldump` في دقيقة. مع row-level، ده **مشروع استخراج بـ 20 جدول مفلتر** كل مرة — وهو نفسه المطلوب لـ GDPR data export/delete.

**8. النسخ الاحتياطي والاستعادة النقطية.**
عميل مسح 300 عقار بالغلط → استعادة قاعدته وحدها. في shared DB، الاستعادة النقطية لعميل واحد من نسخة كاملة هي عملية يدوية مؤلمة كل مرة.

### التكاليف اللي بتقبلها بوضوح مع الاختيار ده

| التكلفة | الحجم الفعلي | التخفيف |
|---|---|---|
| N قاعدة بيانات | MySQL على VPS بيستحمل مئات القواعد ببساطة. عند 500+ tenant تحتاج sharding | راقب من بدري؛ الحد ده بعيد عن أول 3 سنين |
| المايجريشنز على N قاعدة | `tenants:migrate` بياخد دقايق عند N=200 وممكن يفشل جزئيًا | migration runner بيتتبّع حالة كل tenant + إعادة تشغيل للفاشلين + maintenance mode لكل tenant |
| التحليلات cross-tenant | مستحيلة بكويري واحدة | job ليلي بيجمّع (عدد العقارات، leads، مساحة، pageviews) في `usage_metrics` بالقاعدة المركزية |
| اتصالات الداتابيز | تبديل الاتصال كل ريكوست | استخدم نفس credentials + `USE database` — ثابت التكلفة |
| منحنى تعلّم stancl | 3-5 أيام لفهم الـ bootstrappers صح | محسوبة في تقدير المرحلة 1 |

### ❌ ليه رافض المسار 1 (قاعدة واحدة + `tenant_id`)

**مش لأنه صعب — لأن كلفة الخطأ فيه غير محدودة، والفريق صغير، ومفيش شبكة أمان.**

- بيطلب تعديل **8 موديلات + 8 مايجريشنز + `ResourceController` + `Catalog` + 3 فهارس unique + مولّد الـ slug + spatie teams mode + كاش الإعدادات** — أوسع بكتير من التقدير الشائع.
- خطر التسريب **تراكمي مدى الحياة**: كل كويري جديدة يكتبها أي مطوّر مستقبلي، وكل `withoutGlobalScopes()`، وكل `DB::table()` خام، وكل `whereHas` بيخترق الـ scope — كلها نقاط فشل جديدة. مع **صفر اختبارات و صفر CI**، ده رهان مش قرار هندسي.
- `ResourceController.php:115,137,151` — تلات `findOrFail($id)` عارية في الكلاس اللي **كل** الموديولات بترثه. Global scope بيغطيهم، لكن `findOrFail` مع `withoutGlobalScopes` أو أي `join` بيرجّع الخطر فورًا.
- **الفايدة الوحيدة الحقيقية** (تحليلات cross-tenant بكويري واحدة) بتتحقق بـ 90% من job تجميعي ليلي بيكتب في القاعدة المركزية — بجزء بسيط من المخاطرة.
- الحجة الشائعة "أرخص في التشغيل" مش منطبقة هنا: البيانات لكل وكالة **صغيرة جدًا** (مئات العقارات، آلاف الـ leads). مفيش وفر تشغيلي يستاهل المخاطرة دي.

### ❌ ليه رافض المسار 3 (نسخة لكل عميل — الحالي مع نشر أوتوماتيكي)

**ببساطة: ده مش SaaS، ده اللي عندك دلوقتي.**

- كل إصلاح باج = **N نشرة**. عند 30 عميل، أسبوعك كله نشر. عند 50، بتوقف تصلح باجات.
- مفيش تسجيل ذاتي، مفيش metering، مفيش feature gating، مفيش MRR قابل للتوقع. الوحدات الاقتصادية بتفضل **خدمات (services)** مش **منتج (product)** — والتقييم والهوامش مختلفين جوهريًا.
- `setup.sh:19` بيعمل `composer create-project` من الشبكة — مستحيل يقف خلف زرار signup.
- كل عميل = نسخة كود + قاعدة + شهادة SSL + VPS/hosting + مراقبة منفصلة. تكلفة التشغيل **خطية**، وهي عكس تعريف SaaS.
- **بس:** احتفظ بيه كـ **طبقة Enterprise / On-Premise** لأكبر 3-5 عملاء اللي بيدفعوا مقابل عزل كامل أو استضافة عندهم. المعمارية الموصى بها بتخلّي ده سهل: نفس الكود، tenant واحد في نسخته.

---

## 5. خطة الهجرة على مراحل

### المرحلة 0 — تأسيس قبل أي tenancy · **20 يوم**

مينفعش تبدأ tenancy على SQLite بصفر اختبارات وبدون رفع ملفات.

| الشغل | الملفات المتأثرة |
|---|---|
| التحويل لـ MySQL 8 كافتراضي + التحقق من كل مايجريشن | `engine/config/database.php:20`, `engine/.env.example`, `engine/setup.sh:65-69` |
| Media uploader حقيقي بـ medialibrary — `InteractsWithMedia` على 6 موديلات، حقل رفع في الفورم بدل النص | `engine/app/Support/ResourceController.php:203-244` (نوع `image`), `engine/resources/js/Pages/Admin/Resource/Form.tsx:110-125`, كل الموديلات في `Modules/*/app/Models/`, ونشر `config/media-library.php` |
| Rate limiting + honeypot على الفورم العام واللوجن | `engine/routes/web.php:60`, `engine/Modules/Core/routes/web.php:21`, `engine/bootstrap/app.php` |
| Escaping/whitelist لقيم الثيم قبل الحقن (سد ثغرة CSS injection) | `engine/resources/views/app.blade.php:22-28`, `engine/Modules/Core/app/Http/Controllers/SettingsController.php:101-104` |
| Test suite أساسي: تغطية كل الـ CRUD والأوث | `engine/tests/` (بديل الـ stubs), `engine/phpunit.xml` |
| CI: GitHub Actions — Pint + PHPUnit + `vite build` | ملف جديد `.github/workflows/ci.yml` |

**المخاطر:** التحويل من SQLite لـ MySQL بيكشف مشاكل نوع بيانات مستخفية (`json` casts في `Setting.php:13`, `boolean` vs `tinyint`, طول `unique` على `varchar` عربي). متوسطة، بتتكشف بسرعة.

### المرحلة 1 — نواة الـ tenancy · **22 يوم**

| الشغل | الملفات المتأثرة |
|---|---|
| تركيب `stancl/tenancy` + قاعدة مركزية + موديل `Tenant`/`Domain` | `engine/composer.json`, `engine/config/tenancy.php` (جديد), `engine/bootstrap/providers.php` |
| نقل الـ 22 جدول لـ `database/migrations/tenant/` | كل `Modules/*/database/migrations/*`, `database/migrations/*` |
| InitializeTenancyByDomain في الـ middleware stack | `engine/bootstrap/app.php:14-26` |
| فصل راوتات الـ tenant عن الـ central | `engine/routes/web.php`, `engine/Modules/*/routes/web.php` (10 ملفات), وكل `RouteServiceProvider` في الموديولات |
| تفعيل bootstrappers: Cache · Session · Queue · Filesystem · Redis | `engine/config/tenancy.php`, والتحقق من `SettingsService.php:18,35` بعد التفعيل |
| فصل الـ super admin guard على دومين منفصل | `engine/config/auth.php:40-50`, `engine/bootstrap/app.php:25` |
| **اختبارات عزل صريحة** — tenant A مايقدرش يشوف بيانات B عبر كل راوت | `engine/tests/Feature/Tenancy/` (جديد) |

**المخاطر:** 🔴 عالية على `SettingsService` — لو الـ CacheBootstrapper مش متضبوط صح، تسريب الثيم (§3.1) بيحصل صامت في البرودكشن. **لازم اختبار صريح**: عميلين، لونين، ريكوستين متتاليين على نفس الـ worker.
🟠 متوسطة على `nwidart/laravel-modules` — الـ RouteServiceProviders (`Modules/Core/app/Providers/RouteServiceProvider.php:36-39`) بتسجّل راوتات بـ `Route::middleware('web')` مباشرة، ولازم تتلفّ في مجموعة الـ tenant. تكامل الحزمتين مش موثّق كويس ومحتاج ضبط يدوي.

### المرحلة 2 — Control plane + provisioning · **20 يوم**

| الشغل | الملفات المتأثرة |
|---|---|
| Super admin panel: قائمة tenants، impersonation، تعليق/تفعيل، لوج | تطبيق جديد تحت `Modules/Platform/` (موديول جديد) |
| Signup ذاتي + provisioning job (< 30 ثانية) | يستبدل `setup.sh:19-70` بالكامل |
| Seeding لكل tenant جديد: أدوار + إعدادات افتراضية + محتوى تجريبي | يستبدل `Modules/Core/database/seeders/AdminUserSeeder.php:17-25` (**شيل `admin@bp-eg.com` / `password` نهائيًا**) |
| Migration runner لكل الـ tenants بتتبّع حالة وإعادة تشغيل | أمر artisan جديد |
| Job تجميعي ليلي للاستخدام في القاعدة المركزية | جديد |

**المخاطر:** provisioning جزئي الفشل (قاعدة اتعملت، seeding فشل) بيسيب tenants "زومبي". لازم transaction/تعويض واضح. متوسطة.

### المرحلة 3 — الدومينات + الـ SSL + SEO لكل tenant · **18 يوم**

| الشغل | الملفات المتأثرة |
|---|---|
| Subdomain wildcard + custom domain + تحقق DNS | `engine/config/tenancy.php`, Caddy/Traefik/nginx config |
| ACME أوتوماتيك (Caddy on-demand TLS أو Traefik) | infra — خارج الريبو |
| `robots.txt` ديناميكي لكل دومين | يستبدل `engine/public/robots.txt` براوت |
| `sitemap.xml` مولّد من عقارات وكمبوندات ومقالات الـ tenant | `Modules/Seo/app/Http/Controllers/SeoController.php:13-55` (دلوقتي stubs فاضية) |
| canonical + hreflang `/ar`↔`/en` + Open Graph + `RealEstateListing` schema | `engine/resources/views/app.blade.php:11-37`, `engine/app/Http/Middleware/SetLocale.php` |
| فصل assets: لوجو وفيديوهات لكل tenant | `engine/public/images/`, `engine/public/videos/`, `engine/config/filesystems.php:41-48` |

**المخاطر:** on-demand TLS بيحتاج endpoint تحقق مضبوط وإلا Let's Encrypt بيعمل rate limit عليك. متوسطة، بس مؤلمة أول مرة.

**⛳ نهاية المرحلة 3 = SaaS شغّال (فواتير يدوية).** المجموع لحد هنا: **80 يوم**. ينفع تشغّل بيه أول 10-20 عميل مدفوعين بتحصيل يدوي.

### المرحلة 4 — الخطط والفوترة · **28 يوم**

| الشغل | الملفات المتأثرة |
|---|---|
| `plans`, `features`, `subscriptions`, `invoices` في القاعدة المركزية | `Modules/Platform/` |
| Feature gating middleware + حدود (عدد العقارات/المستخدمين/المساحة) | `engine/app/Support/ResourceController.php:123` (store) |
| **White-label كميزة مدفوعة** — نقل الفوتر لإعداد مربوط بالخطة | `engine/resources/js/Layouts/SiteLayout.tsx:248-262`, `engine/resources/js/Layouts/AdminLayout.tsx:63-68`, `SettingsController.php:16-24` |
| بوابة دفع + فواتير + dunning + تعليق تلقائي | جديد |

**بوابة الدفع — قرار السوق المصري:**

| البوابة | الموقف الواقعي |
|---|---|
| **Paymob** ✅ | **الاختيار الأساسي.** مدفوعات محلية بالجنيه، فيزا/ماستر محلية، محافظ (فودافون كاش/إنستاباي)، تقسيط (فاليو/سيمبل). API معقول، وثائق ماشية. **بس مفيش subscription engine حقيقي** — لازم تبني دورة الفوترة والتجديد بنفسك (وده بند كبير من الـ 28 يوم) |
| **Fawry** ✅ | **مكمّل مش بديل.** الدفع النقدي عند المنافذ حاسم للوكالات خارج القاهرة/الإسكندرية. التسوية أبطأ، والتكامل أثقل. حطه في المرحلة 4.5 |
| **Stripe** ❌ | **مش متاح للشركات المصرية كـ merchant** (Atlas بيحل ده بكيان أمريكي — تعقيد قانوني وضريبي حقيقي، مش مجرد paperwork). subscription engine بتاعه هو الأفضل عالميًا، وده الخسارة الحقيقية |
| **Paddle** 🟡 | Merchant of Record — بيتولّى الضرايب والفواتير. **مفيد لو هتبيع خارج مصر**. بس بيقبل بطاقات دولية بس، ووكالة في المنصورة معندهاش بطاقة دولية. **ومصر بتقيّد التحصيل بالدولار من عملاء محليين** |

**التوصية:** ابدأ بـ **Paymob بالجنيه** + دورة اشتراك مبنية داخليًا. ضيف Fawry بعد أول 20 عميل. سيب Paddle لو/لما تفتح خارج مصر. **وفي أول 6 شهور: تحويل بنكي/إنستاباي يدوي + فاتورة PDF هو حل صالح تمامًا** — 3 أيام شغل بدل 28، ومش بيمنعك من البيع. أجّل المرحلة دي لحد ما يبقى معاك ≥10 عملاء دافعين.

**المخاطر:** 🔴 عالية. Paymob webhooks reconciliation، المرتجعات، الفواتير الضريبية المصرية (منظومة الفاتورة الإلكترونية إجبارية للشركات) — دي كلها أعمق مما تبدو.

### المرحلة 5 — التشغيل والموثوقية · **25 يوم**

| الشغل | الملفات المتأثرة |
|---|---|
| Backups لكل tenant (يومي + استعادة نقطية مختبَرة) | infra + أمر artisan |
| Error tracking (Sentry) بـ tenant context في كل حدث | `engine/config/logging.php:21`, `engine/bootstrap/app.php:27-28` (الـ `withExceptions` فاضي دلوقتي) |
| لوجات منظّمة (JSON) + tenant_id + rotation | `engine/config/logging.php:53-126` |
| Redis للكاش والكيو + طوابير معزولة | `engine/config/cache.php:18`, `engine/config/queue.php:16,39-42` |
| Horizon + مراقبة الفشل لكل tenant | جديد |
| Staging + نشر أوتوماتيكي + rollback | `.github/workflows/` |
| Uptime/APM + تنبيهات | خارج الريبو |
| إيميل حقيقي (SES/Postmark) + قوالب معاملات | `engine/config/mail.php`, `engine/.env` (`MAIL_MAILER=log` حاليًا) |
| SMS/OTP + WhatsApp Business API (بدل رابط `wa.me` في الفرونت) | `Modules/Leads/` |

**المخاطر:** استعادة نسخة احتياطية **مش مختبَرة** = مافيش نسخة احتياطية. اعمل drill حقيقي.

### المرحلة 6 — الامتثال والتشطيب · **12 يوم**

| الشغل |
|---|
| تصدير بيانات كامل لكل tenant (self-serve) |
| حذف/مجهولية البيانات + retention policy |
| سياسة خصوصية + شروط استخدام + DPA (وقانون حماية البيانات المصري 151/2020) |
| اختبار حِمل عند N=100 وN=500 tenant (خصوصًا زمن `tenants:migrate`) |
| ضبط أداء: تحسين الاستعلامات، فهارس، HTTP caching، صور responsive |

---

## 6. الناقص لتشغيل SaaS كمنتج تجاري (مرتّب بالأولوية)

**P0 — من غيرهم مفيش منتج تبيعه**
1. **تسجيل ذاتي + provisioning أوتوماتيكي** — `setup.sh` سكريبت باش (`setup.sh:19-70`) مينفعش خلف زرار
2. **لوحة إدارة المنصة (super admin)** — قائمة العملاء، تعليق، impersonation، لوج. **صفر منها موجود**
3. **دومين لكل عميل + SSL أوتوماتيك** — الوكالة عايزة `alsaqr-realestate.com` مش `tenant-17.app`. مفيش `Route::domain()` ولا domain model
4. **رفع الميديا** — أساس في أي CMS، و**مش موجود** (`Form.tsx:110-116` حقل نصّي)
5. **نسخ احتياطي واستعادة لكل عميل** — صفر
6. **تتبّع الأخطاء + لوجات منظّمة** — `LOG_CHANNEL=stack → single`، مفيش Sentry، `withExceptions` فاضي في `bootstrap/app.php:27`
7. **CI + بيئة staging + اختبارات** — مفيش `.github/`، واختبارين stub

**P1 — من غيرهم المنتج بيتسرّب فلوس أو عملاء**
8. **خطط + feature gating + حدود استخدام** — مفيش أي مفهوم "خطة" في الكود
9. **الفوترة (Paymob بالجنيه)** — يدوي مقبول لأول 10 عملاء بس
10. **White-label كترقية مدفوعة** — الفوتر الإلزامي (`SiteLayout.tsx:248-262`) لازم يتحوّل لإعداد مربوط بالخطة
11. **SEO لكل دومين** — sitemap + robots ديناميكي + canonical + hreflang + `RealEstateListing` schema. **صفر منها** (`SeoController.php` stubs، `robots.txt` ستاتيك)
12. **Rate limiting + مكافحة السبام** — صفر throttle في الكود كله
13. **إيميل حقيقي** — `MAIL_MAILER=log`، يعني مفيش welcome ولا reset ولا تنبيه lead
14. **قياس الاستخدام (metering)** — لازم للفوترة وللدعم وللتسعير

**P2 — لازمين للنمو والاحتراف**
15. **قياس الأداء عند N** — `tenants:migrate` عند 200 عميل، خطة الاتصالات، فهارس
16. **الامتثال** — تصدير/حذف البيانات، قانون 151/2020 المصري
17. **WhatsApp Business API حقيقي** — دلوقتي رابط `wa.me` مبني في الفرونت بس
18. **SMS/OTP** — لتأكيد أرقام الوكلاء والعملاء المحتملين
19. **بوابة دعم + توثيق + onboarding داخل المنتج** — بدونهم كل عميل بيبقى مكالمة تليفون
20. **إكمال الموديولات الفاضية** — `Pages`, `Reviews`, `Seo` فيها `Http` و`Providers` بس، **مفيش موديلات ولا مايجريشنز**. تباع كـ features وهي مش موجودة

---

## 7. التقدير الإجمالي

### أيام المطور

| المرحلة | يوم |
|---|---|
| 0 — تأسيس (MySQL · ميديا · rate limit · اختبارات · CI) | 20 |
| 1 — نواة الـ tenancy | 22 |
| 2 — Control plane + provisioning | 20 |
| 3 — الدومينات + SSL + SEO لكل tenant | 18 |
| **مجموع فرعي — SaaS شغّال بفواتير يدوية** | **80** |
| 4 — الخطط + الفوترة (Paymob) | 28 |
| 5 — التشغيل والموثوقية | 25 |
| 6 — الامتثال والتشطيب | 12 |
| **الإجمالي الخام** | **145** |
| **+ 20% buffer** (تكامل stancl مع nwidart، Paymob، ACME) | **~175** |

### الأشهر التقويمية

| الفريق | MVP (80 يوم) | كامل تجاريًا (175 يوم) |
|---|---|---|
| **مطور واحد full-time** | 4 شهور | **8–9 شهور** |
| **اتنين (backend + frontend/ops)** | 2.5 شهر | **4.5–5 شهور** |
| **تلاتة** | 2 شهر | 3.5 شهر (تنسيق زايد، عائد متناقص) |

### التوصية بالتسلسل

**مطور واحد** هو الأنسب للمرحلة 0-1 (شغل معماري مترابط، والتقسيم بيبطّئه). **ضيف التاني من المرحلة 2** — الـ control plane والـ SEO والـ ops بتتوازى كويس.

**اشحن عند نهاية المرحلة 3 (4 شهور).** حصّل يدوي. بيع لـ 10-20 وكالة. اللي هتتعلمه من العشرة دول عن الخطط والحدود واللي بيدفعوا مقابله **هيغيّر تصميم المرحلة 4**. بناء الفوترة قبل ما تعرف تسعيرك هو **28 يوم على أرض متحركة**.

### حكم الـ rewrite مقابل الـ adaptation

| البند | التصنيف |
|---|---|
| الـ 8 موديلات والـ 8 مايجريشنز | **بتفضل زي ما هي** — الـ tenancy بتتحل على مستوى الاتصال |
| `ResourceController` (245 سطر) | **بتفضل زي ما هي** — مش محتاجة scope مع DB-per-tenant |
| الـ Theme Engine (`app.css` + `app.blade.php`) | **adaptation صغيرة** — الميكانيزم شغال، مصدر القيم بس محتاج ضبط |
| `SettingsService` | **adaptation** — bootstrapper بيصلح الكاش، السطور زي ما هي |
| التوجيه (`routes/web.php` + 10 موديولات) | **adaptation متوسطة** — لفّ في مجموعات tenant/central |
| الميديا | **بناء من الصفر** — مش موجودة (0% مبني رغم إن الحزمة متثبّتة) |
| الفوترة · التسجيل · super admin · الدومينات · SEO · ops | **بناء من الصفر** — 0% منهم موجود |

**الخلاصة:** الـ multi-tenancy **adaptation** — والكود مبني بشكل بيسهّلها. اللي **rewrite/greenfield** هو كل التاني: المنتج التجاري نفسه. وده الجزء اللي بياخد **86% من الـ 145 يوم**.
