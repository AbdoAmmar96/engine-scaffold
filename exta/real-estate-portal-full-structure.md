# هيكل مشروع بوابة عقارية متكاملة
## استنتاج كامل من تحليل URE (ureeg.com) — المستخدمون، الصفحات، والصلاحيات

> **منهجية الوثيقة:** الواجهة العامة (Frontend) مبنية على ما تم رصده فعلياً من الموقع. أما الأدوار الداخلية ولوحة التحكم والصلاحيات فهي **استنتاج هندسي** لما يتطلبه نظام بهذه المواصفات (إعلانات تُراجع قبل النشر، فورم "أضف عقارك"، مستشارون عقاريون، تحديث يومي، إعلانات مميزة).

---

## 1. الكيانات الأساسية (Data Model)

| الكيان | أهم الحقول المستنتجة |
|---|---|
| **Property** (عقار) | title (ar/en), slug, ref_code (URE-R/N####), type (12 نوع), purpose (بيع/إيجار), category (سكني/تجاري), price, monthly_rent, down_payment, monthly_installment, installment_years, area_sqm, bedrooms, bathrooms, finishing (5 مستويات: بدون/نصف/كامل/مفروش/فليكسي), status (draft/pending/published/rejected/sold/rented), is_featured, compound_id, area_id, owner_id, images[], description, views_count |
| **Compound** (مشروع/كمبوند) | name (ar/en), slug, developer_id, area_id, starting_price, min_down_payment, max_installment_years, is_new_launch, is_spotlight, master_plan_image, brochure_url, description, images[], properties_count |
| **Developer** (مطوّر) | name (ar/en), slug, logo, description, projects_count, properties_count |
| **Area** (منطقة) | name (ar/en), slug, parent_id (مناطق فرعية: القاهرة الجديدة ← التجمع الخامس), starting_price, description, properties_count |
| **BlogPost** (مقال) | title, slug, category, content, cover, seo_meta, author_id, published_at |
| **Lead** (طلب عميل) | name, phone, intent (شراء/استئجار/بيع/عرض للإيجار), preferred_area, notes, source_page, status (new/contacted/qualified/closed), assigned_to, channel (فورم/واتساب) |
| **SeoLandingPage** | تركيبة (نوع × غرض × منطقة)، h1, intro_text, meta_title, meta_description — تُولَّد برمجياً |
| **FeaturedAd** (إعلان مميز) | position (hero/listing/sidebar), target (property/compound), starts_at, ends_at, priority |
| **User** | name, email, phone, role, avatar, locale |
| كيانات مساعدة | Favorite, SavedSearch (+ Alerts), RecentlyViewed, Media, Setting, ActivityLog |

---

## 2. المستخدمون الكاملون للنظام (9 أدوار)

| # | الدور | النطاق | الوصف المختصر |
|---|---|---|---|
| 1 | **الزائر** (Guest) | الواجهة العامة | يتصفح ويبحث ويرسل طلبات بدون حساب |
| 2 | **المستخدم المسجل** (Registered User) | الواجهة + منطقة الحساب | مفضلة، بحث محفوظ، تنبيهات، متابعة طلباته |
| 3 | **المعلن / مالك العقار** (Lister) | منطقة الحساب | يضيف عقاراته للمراجعة ويتابع أداءها |
| 4 | **الوسيط / الوكالة** (Agent — اختياري) | منطقة الحساب الموسعة | معلن بصلاحيات جماعية + صفحة وكالة عامة |
| 5 | **المستشار العقاري** (Sales Consultant — موظف) | لوحة التحكم (Leads) | يدير الطلبات المسندة إليه ويتابع العملاء |
| 6 | **مدخل البيانات** (Data Entry — موظف) | لوحة التحكم (محتوى عقاري) | إدخال وتحديث العقارات والمشاريع بدون نشر نهائي |
| 7 | **محرر المحتوى** (Content Editor — موظف) | لوحة التحكم (مدونة + SEO) | المقالات ونصوص صفحات الهبوط |
| 8 | **مسؤول التسويق** (Marketing Manager — موظف) | لوحة التحكم (إعلانات + تقارير) | الإعلانات المميزة، البانرات، البيكسلات، تقارير الأداء |
| 9 | **الأدمن / السوبر أدمن** (Admin / Super Admin) | لوحة التحكم كاملة | الاعتماد والنشر وإدارة المستخدمين؛ السوبر أدمن يضيف الأدوار والإعدادات والتكاملات |

---

## 3. خريطة الصفحات الكاملة لكل مستخدم

### 3.1 الواجهة العامة — متاحة للزائر (وكل من فوقه)

| الصفحة | المسار | ملاحظات |
|---|---|---|
| الرئيسية | `/{ar\|en}/` | 15 قسماً (Hero، بحث، أنواع، مشاريع، Spotlight، كمبوندات، مطورون، مناطق، عقارات، إنجازات، كيف نعمل، شوهدت مؤخراً، لماذا نحن، فورم، Footer) |
| قائمة العقارات | `/properties/` | مع فلاتر متقدمة (سعر، مساحة، غرف، حمامات، تشطيب، مقدم، قسط، سنوات) |
| العقارات التجارية | `/properties/commercial/` | + فرعية: `clinics-for-sale` / `shops-for-sale` / `offices-for-sale` |
| صفحات SEO البرمجية | `/properties/{type}-for-{purpose}/`، `/properties/in-{area}/`، `/properties/{type}-for-{purpose}-in-{area}/` | مولدة تلقائياً لكل تركيبة صالحة |
| تفاصيل عقار | `/property/{slug}` | كود مرجعي، معرض صور، مواصفات، CTA واتساب |
| قائمة الكمبوندات | `/compounds/` | |
| تفاصيل كمبوند | `/compound/{slug}` | ماستر بلان + بروشور + وحدات المشروع |
| قائمة المطورين | `/developers/` | |
| صفحة مطوّر | `/developer/{slug}` | مشاريعه ووحداته |
| قائمة المناطق | `/area/` أو `/areas/` | |
| صفحة منطقة | `/properties/in-{area}/` | تعمل كصفحة هبوط للمنطقة |
| المدونة | `/blog/` + `/blog/{slug}` | |
| أضف عقارك | `/add-property` | فورم عام يتحول لـ Lead أو حساب معلن |
| صفحات ثابتة | `/about`، `/contact`، `/careers`، `/privacy-policy`، `/sitemap/`، `/team/` | |
| تبديل اللغة | `/ar/` ⇄ `/en/` | نسختان كاملتان |

**صلاحيات الزائر:** تصفح كل ما سبق، بحث وفلترة، إرسال Lead عبر الفورم/واتساب، "شوهدت مؤخراً" (localStorage فقط). **لا يستطيع:** حفظ مفضلة دائمة، نشر عقار مباشرة، الوصول لأي لوحة.

### 3.2 صفحات المستخدم المسجل

| الصفحة | المسار المقترح |
|---|---|
| تسجيل / دخول / استعادة كلمة مرور / تفعيل | `/register`، `/login`، `/forgot-password`, `/verify` |
| ملفي الشخصي | `/account` |
| المفضلة | `/account/favorites` |
| عمليات البحث المحفوظة + التنبيهات | `/account/saved-searches` |
| طلباتي (Leads التي أرسلتها) | `/account/inquiries` |
| الإعدادات (لغة، إشعارات، كلمة مرور) | `/account/settings` |

**يضيف فوق الزائر:** حفظ مفضلة، حفظ بحث + تنبيه بريدي/واتساب عند نزول وحدات مطابقة، متابعة حالة طلباته، تعبئة تلقائية للفورم.

### 3.3 صفحات المعلن (Lister) — امتداد لحساب المستخدم

| الصفحة | المسار المقترح |
|---|---|
| عقاراتي | `/account/my-properties` (مسودة/قيد المراجعة/منشور/مرفوض/مباع) |
| إضافة عقار (Wizard) | `/account/my-properties/create` |
| تعديل عقار | `/account/my-properties/{id}/edit` |
| إحصائيات إعلاناتي | `/account/my-properties/{id}/stats` (مشاهدات، Leads) |
| ترقية لإعلان مميز | `/account/my-properties/{id}/feature` |

**صلاحياته:** CRUD على **عقاراته فقط**، والنشر دائماً عبر موافقة الإدارة (pending → review). التعديل بعد النشر يعيد العقار للمراجعة. **الوسيط/الوكالة** = نفس الصفحات + عدد وحدات أكبر، أعضاء فريق، وصفحة وكالة عامة `/agency/{slug}`.

### 3.4 لوحة التحكم (Filament Admin Panel) — `/admin`

| المورد / الصفحة | يظهر لـ |
|---|---|
| Dashboard (KPIs: عقارات، Leads اليوم، مشاهدات، مبيعات) | كل الموظفين (كل حسب نطاقه) |
| Properties (CRUD + قائمة انتظار الاعتماد Approval Queue) | مدخل بيانات (بدون نشر/حذف)، أدمن |
| Compounds / Projects | مدخل بيانات، أدمن |
| Developers | مدخل بيانات، أدمن |
| Areas | مدخل بيانات (إضافة/تعديل)، أدمن |
| Blog Posts + Categories | محرر المحتوى، أدمن |
| SEO Landing Pages (نصوص وميتا الصفحات البرمجية) | محرر المحتوى، تسويق، أدمن |
| Leads (Pipeline: جديد/تم التواصل/مؤهل/مغلق + إسناد) | المستشار (المسند له فقط)، تسويق (قراءة/تصدير)، أدمن |
| Featured Ads / Banners | تسويق، أدمن |
| Popular Searches (روابط "الأكثر بحثاً") | تسويق، محرر، أدمن |
| Users (عملاء + معلنون) | أدمن |
| Staff & Roles (فريق العمل والأدوار) | سوبر أدمن فقط |
| Media Library | مدخل بيانات، محرر، أدمن |
| Reports (أداء، مصادر Leads، أكثر المناطق طلباً) | تسويق، أدمن |
| Settings (بيانات الموقع، واتساب، GTM/Pixels، لغات) | سوبر أدمن (التسويق: حقول التتبع فقط) |
| Activity Log | أدمن (قراءة)، سوبر أدمن |

---

## 4. مصفوفة الصلاحيات (Permissions Matrix)

**الرموز:** ✓ = صلاحية كاملة · Own = على سجلاته فقط · R = قراءة فقط · — = لا يوجد

### 4.1 أدوار الواجهة (Frontend)

| الإجراء | زائر | مسجل | معلن | وسيط |
|---|---|---|---|---|
| تصفح وبحث وفلترة | ✓ | ✓ | ✓ | ✓ |
| إرسال Lead / واتساب | ✓ | ✓ | ✓ | ✓ |
| مفضلة + بحث محفوظ + تنبيهات | — | ✓ | ✓ | ✓ |
| إنشاء عقار (يدخل مراجعة) | — | — | Own | Own |
| تعديل / أرشفة عقار | — | — | Own | Own |
| نشر نهائي بدون مراجعة | — | — | — | — |
| إحصائيات الإعلانات | — | — | Own | Own |
| طلب إعلان مميز | — | — | Own | Own |
| صفحة وكالة عامة + فريق | — | — | — | ✓ |

### 4.2 أدوار لوحة التحكم (Staff)

| المورد → الإجراء | مدخل بيانات | محرر محتوى | تسويق | مستشار | أدمن | سوبر أدمن |
|---|---|---|---|---|---|---|
| Properties: view / create / update | ✓ | R | R | R | ✓ | ✓ |
| Properties: **approve / publish** | — | — | — | — | ✓ | ✓ |
| Properties: delete / feature | — | — | feature فقط | — | ✓ | ✓ |
| Compounds & Developers & Areas: CRUD | ✓ (بدون حذف) | R | R | R | ✓ | ✓ |
| Blog: CRUD + publish | — | ✓ | R | — | ✓ | ✓ |
| SEO Pages: تحرير النصوص والميتا | — | ✓ | ✓ | — | ✓ | ✓ |
| Leads: view | — | — | ✓ (الكل) | Own (المسند له) | ✓ | ✓ |
| Leads: update status / notes | — | — | — | Own | ✓ | ✓ |
| Leads: assign / export | — | — | export | — | ✓ | ✓ |
| Featured Ads & Banners: CRUD | — | — | ✓ | — | ✓ | ✓ |
| Users (عملاء/معلنون): إدارة وحظر | — | — | — | — | ✓ | ✓ |
| Staff & Roles & Permissions | — | — | — | — | — | ✓ |
| Media Library | ✓ | ✓ | ✓ | — | ✓ | ✓ |
| Reports | — | — | ✓ | Own | ✓ | ✓ |
| Settings (عام / تكاملات) | — | — | حقول التتبع | — | R | ✓ |
| Activity Log | — | — | — | — | R | ✓ |

---

## 5. تسمية الصلاحيات برمجياً (spatie/laravel-permission + Filament Shield)

```
# Properties
view_any_property, view_property, create_property, update_property,
delete_property, approve_property, publish_property, feature_property,
export_property

# Compounds / Developers / Areas (نفس النمط)
view_any_compound, create_compound, update_compound, delete_compound ...

# Blog
view_any_blog_post, create_blog_post, update_blog_post,
delete_blog_post, publish_blog_post

# Leads
view_any_lead, view_own_lead, update_lead_status, assign_lead, export_lead

# Featured Ads
view_any_featured_ad, create_featured_ad, update_featured_ad, delete_featured_ad

# Users & Roles
view_any_user, update_user, ban_user, view_any_role, update_role

# Settings & Logs
manage_settings, manage_tracking_settings, view_activity_log
```

**التنفيذ في Filament v3:** Policy لكل Model مربوطة بهذه المفاتيح، + `filament-shield` لتوليدها وربطها بالـ Resources تلقائياً، وحساب المعلن يُخدم إما بـ Panel منفصل (`/portal`) أو بمنطقة حساب Livewire في الواجهة مع Scoping على `owner_id`.

---

## 6. الـ Workflows الأساسية

1. **نشر عقار من معلن:** إنشاء (draft) → إرسال للمراجعة (pending) → مدخل البيانات يكمل النواقص → الأدمن يعتمد أو يرفض بسبب → نشر + توليد ref_code + إشعار للمعلن. أي تعديل لاحق يعيد الحالة إلى pending.
2. **دورة الـ Lead:** فورم الموقع / زر واتساب → تسجيل Lead بمصدره (source_page) → إسناد تلقائي أو يدوي لمستشار → تحديث الحالة (جديد → تم التواصل → مؤهل → مغلق) → تقارير تحويل للتسويق.
3. **الإعلان المميز:** طلب من المعلن أو قرار داخلي → تحديد الموضع (Hero / أول النتائج) والمدة → عرض مجدول → إحصائيات أداء.
4. **صفحات SEO البرمجية:** عند إضافة منطقة أو نوع جديد تتولد تلقائياً كل التركيبات الصالحة (نوع × غرض × منطقة) بشرط وجود وحدات فعلية، مع إمكانية تحرير نصوصها يدوياً من لوحة المحرر.

---

## 7. ملاحظات تنفيذية على ستاك Laravel + Filament

- **الترجمة:** `spatie/laravel-translatable` للكيانات (ar/en) مع مسارات `/{locale}/` وhreflang.
- **الأكواد المرجعية:** Observer يولّد `URE-R####` (إعادة بيع) و`URE-N####` (جديد/إيجار) عند الاعتماد.
- **الفلاتر:** Query Scopes + فهارس مركبة على (type, purpose, area_id, price) لأداء صفحات الـ SEO.
- **شوهدت مؤخراً:** localStorage للزائر، ومزامنة بجدول عند تسجيل الدخول.
- **الفورم → واتساب:** الحفظ في قاعدة البيانات أولاً (Lead) ثم فتح `wa.me` برسالة مولدة — حتى لا تضيع البيانات إن لم يكمل المستخدم الإرسال.
- **التتبع:** GTM Container منفصل للموقع وآخر للإعلانات (كما في URE) مع DataLayer events: `lead_submit`, `whatsapp_click`, `property_view`, `filter_apply`.
