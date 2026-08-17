# BP Engine — Phase 1 Scaffold
Laravel 12 + Inertia v2 + React TS + Vite + Tailwind v4 · Modular MVC (nwidart) · Theme Engine · داشبورد React مخصوص (بدون Filament)

© شركة شريك الأعمال لتقنية المعلومات — bp-eg.com

---

## التشغيل (3 خطوات)

```bash
# المتطلبات: PHP 8.3+ · Composer · Node 20+   (Laravel Herd عندك بيغطيهم)
chmod +x setup.sh
./setup.sh engine        # اسم فولدر المشروع — اختياري
cd engine && composer run dev
```

| | |
|---|---|
| الموقع | http://localhost:8000/ar (و /en) |
| الداشبورد | http://localhost:8000/admin |
| الدخول | admin@bp-eg.com / password ← **غيّرها فورًا** |
| قاعدة البيانات | SQLite جاهزة — للتحويل لـ MySQL عدّل `.env` وشغّل `php artisan migrate:fresh --seed` |

## أول تجربة تعملها (إثبات الـ Theme Engine)
1. افتح الداشبورد → الإعدادات → **الهوية والألوان**.
2. غيّر "اللون الأساسي" من الذهبي لأي لون.
3. احفظ، وافتح الموقع واعمل ريفريش — **الموقع كله اتغير من غير أي build**.

## إيه اللي جوه الحزمة

```
setup.sh                 ← ينشئ Laravel 12 + يثبت الحزم + يولّد 10 موديولات + يركّب الملفات + migrate/seed
overlay/
├── bootstrap/app.php                    Inertia middleware + role/permission aliases
├── routes/web.php                       راوتات الموقع بـ /ar /en + تحويل الجذر
├── app/Http/Middleware/                 HandleInertiaRequests (shared props) + SetLocale
├── app/Models/User.php                  + HasRoles
├── resources/views/app.blade.php        حقن توكنز الثيم من DB + خط Cairo + GTM من الإعدادات
├── resources/css/app.css                Tailwind v4 @theme inline ← قلب الـ Theme Engine
├── vite.config.ts · tsconfig.json
├── resources/js/
│   ├── app.tsx · ssr.tsx
│   ├── Layouts/SiteLayout.tsx           هيدر + سويتشر لغة + فوتر حقوق شريك الأعمال (إلزامي)
│   ├── Layouts/AdminLayout.tsx          سايدبار RTL + ناڤ الموديولات
│   ├── Pages/Site/Home.tsx              هيرو مؤقت بتوكنز الثيم (ع/EN)
│   ├── Pages/Admin/{Login,Dashboard}.tsx
│   ├── Pages/Admin/Settings/Edit.tsx    شاشة إعدادات ديناميكية (ألوان تلقائيًا لأي قيمة #hex)
│   └── Components/admin/                ui.tsx (Form Kit v0) + ResourceTable.tsx (Table Kit v0)
├── Modules/Core/
│   ├── routes/web.php                   /admin: auth + dashboard + settings
│   ├── app/Models/Setting.php
│   ├── app/Services/SettingsService.php كاش دائم + فلش تلقائي
│   ├── app/Http/Controllers/            Auth · Dashboard · Settings
│   └── database/                        migration settings + Seeders (Palette A + Cairo + admin user)
└── database/seeders/DatabaseSeeder.php
```

## قرارات مقصودة في المرحلة دي
- **Auth مبسطة** (session + role:admin) — Fortify + 2FA بيتركبوا في المرحلة 2 من غير ما يتغير أي راوت.
- **ResourceTable v0** مبني يدوي server-driven — TanStack بيتركب مكانه بنفس الـ API لما الجداول تتعقد.
- الموديولات التسعة الباقية متولّدة فاضية — كل مرحلة بتملى بتاعها.

## الخريطة الجاية
- **المرحلة 2:** Media Manager + Menu Builder + Users/Roles UI + Fortify/2FA + Activity Log
- **المرحلة 3:** Block Builder (dnd-kit) + المعاينة الحية + أنماط الهيرو (static/slider/video)
- **المرحلة 4:** Locations → Developers → Compounds → Properties (أدمن CRUD + صفحات عامة + فلاتر)
- **المرحلة 5+:** Leads/واتساب → Blog/SEO/SSR → WebGL hero → Playwright → Deploy

---

## v1.1 — الموقع فاتح + 4 صفحات جديدة
- **الثيم الافتراضي بقى أبيض بالكامل** (هيرو وأقسام وفوتر فواتح) — الكحلي والدهبي بقوا للنصوص والأزرار. الرجوع للداكن في أي وقت = تغيير `bg / surface / text` من شاشة الهوية والألوان.
- **صفحات جديدة:** `/properties` (فلاتر UI + كروت عقارات) · `/compounds` (كروت مشاريع بسعر البداية والمقدم والتقسيط) · `/about` · `/contact` (فورم بيبني رسالة واتساب فعلية من بيانات الإعدادات).
- بيانات العقارات والكمبوندات تجريبية من `app/Support/DemoContent.php` (أسماء مشاريع خيالية) — المرحلة 4 بتبدلها بالموديلات الحقيقية بنفس الـ props.
- الهيدر فيه ناڤ كامل + منيو موبايل، والفوتر فاتح بثلاث أعمدة + سطر الحقوق.

---

## v1.2 — WebGL + أنيميشن + اللوجو والفيديو
- **هيرو WebGL حي**: شادر خام (بدون three.js — صفر dependencies إضافية) بيرسم بقع ذهبي/كحلي متحركة فوق الخلفية الفاتحة، بيقرأ الألوان من توكنز الثيم تلقائيًا. Lazy-loaded، بيحترم reduced-motion، DPR cap 1.5، وبيتوقف لما يخرج من الشاشة. تشغيل/إيقاف من الداشبورد: `hero_variant = webgl / static`.
- **أنيميشن**: `Reveal` (ظهور عند التمرير بدون مكتبات) على كل الأقسام + `CountUp` عدادات متحركة للإحصائيات + hover lift على الكروت.
- **اللوجو الحقيقي متركّب**: `public/images/logo.png` (مقصوص، خلفية شفافة، 512px) — ظاهر في الهيدر والهيرو، ومساره بيتغير من الداشبورد → اللوجو والميديا.
- **قسم الفيديو التعريفي** في الرئيسية: حط رابط mp4 أو YouTube في الداشبورد → اللوجو والميديا → رابط الفيديو، وهيظهر فورًا (لحد ما يترفع، البلوك بيعرض مكانه الجاهز).
- **أقسام جديدة في الرئيسية**: أحدث العقارات (3) · الفيديو · أحدث الكمبوندات (2) · كيف نعمل (3 خطوات) — والكروت بقت مكونات مشتركة (`PropertyCard` / `CompoundCard`) بتتستخدم في كل الصفحات.
