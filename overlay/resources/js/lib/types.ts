export type SettingsGroups = Record<string, Record<string, string>>;

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    /** صلاحيات المستخدم — بتحدد اللي بيظهر في سايدبار اللوحة */
    can: string[];
    /** بيفتح لوحة التحكم؟ العميل لأ — بيشوف «حسابي» بدلها */
    staff: boolean;
    /** أرقام الوحدات المحفوظة — عشان القلب يطلع مليان من أول رندر */
    favorites: number[];
}

/** طلب في صفحة «طلباتي» */
export interface AccountRequest {
    id: number;
    subject: string;
    /** رابط الوحدة/المشروع، أو null لو استفسار عام */
    link: string | null;
    message: string;
    status: { label: string; tone: string };
    date: string;
}

/** لينك في قائمة الهيدر أو الفوتر — بيتدار من /admin/menus */
export interface MenuLink {
    label: string;
    url: string;
    external: boolean;
    newTab: boolean;
}

export interface SharedProps {
    settings: SettingsGroups;
    locale: "ar" | "en";
    menu: { header: MenuLink[]; footer: MenuLink[] };
    /** ميتا الصفحة — بتترندر في السيرفر، وبتستخدم هنا لعنوان التاب بس */
    meta?: { title: string; description: string; canonical: string };
    auth: { user: AuthUser | null };
    flash: { success?: string | null; error?: string | null };
    [key: string]: unknown;
}

export interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    from: number | null;
    to: number | null;
}

export interface Property {
    id: number;
    /** رابط صفحة العقار — /{locale}/properties/{slug} */
    slug: string;
    title: string;
    area: string;
    purpose: string;
    /** نوع العقار باللغة المعروضة (شقة / Apartment) */
    type: string;
    /** residential | commercial — مشتق من النوع */
    category: string;
    price: string;
    beds: number;
    baths: number;
    size: number;
    ref: string;
    image: string;
    /** مطوّر الوحدة، وإلا مطوّر الكمبوند — فاضي لو الاتنين مش متحددين */
    developer: string;
}

/** العقار كامل في صفحة التفاصيل */
export interface PropertyDetail extends Property {
    description: string;
    features: string[];
    /** الصورة الرئيسية أول عنصر دايمًا */
    gallery: string[];
    compound: { name: string; slug: string; developer: string; delivery: string } | null;
}

export interface Compound {
    id: number;
    /** رابط صفحة الكمبوند — /{locale}/compounds/{slug} */
    slug: string;
    name: string;
    developer: string;
    area: string;
    starting: string;
    down: string;
    years: string;
    new: boolean;
    image: string;
    desc: string;
    delivery: string;
}

/** الكمبوند كامل في صفحة التفاصيل */
export interface CompoundDetail extends Compound {
    features: string[];
    gallery: string[];
}

/** بطاقة منطقة في قسم "مناطق بنغطيها" بالرئيسية */
export interface Area {
    id: number;
    slug: string;
    name: string;
    note: string;
    count: string;
    image: string;
    /** رابط صفحة المنطقة */
    url: string;
    compounds?: number;
    properties?: number;
}

/** المنطقة كاملة في صفحتها */
export interface AreaDetail {
    id: number;
    slug: string;
    name: string;
    note: string;
    about: string;
    image: string;
    cover: string;
    url: string;
    properties: number;
    compounds: number;
    developers: number;
}

/** كارت مطوّر — في صفحة المطوّرين وفي «من نحن» */
export interface DeveloperCard {
    id: number;
    slug: string;
    name: string;
    /** نبذة المطوّر — بتفضل فاضية لحد ما تتكتب من الداشبورد */
    about: string;
    /** مسار اللوجو — فاضي يعني ارسم أول حرف بدله */
    logo: string;
    /** عدد المشاريع المنشورة */
    compounds: number;
    /** عدد الوحدات المعروضة — بيتبعت في صفحة المطوّرين بس */
    units?: number;
    /** رابط صفحة المطوّر — /{locale}/developers/{slug} */
    url: string;
}

/** المطوّر كامل في صفحته */
export interface DeveloperDetail extends DeveloperCard {
    cover: string;
    website: string;
    founded: string;
    headquarters: string;
    units: number;
    areas: number;
}

/** كارت مقال في المدونة */
export interface BlogPost {
    id: number;
    title: string;
    slug: string;
    category: string;
    excerpt: string;
    image: string;
    author: string;
    date: string;
    /** تاريخ ISO — للـ JSON-LD و<time> */
    publishedAt: string | null;
    /** وقت القراءة بالدقايق */
    read: number;
}

/** المقال كامل بصفحة العرض */
export interface BlogArticle extends BlogPost {
    body: string;
}

/** خيارات البحث في الهيرو */
export interface SearchOptions {
    types: string[];
    locations: string[];
    stats: { value: string; suffix: string; label: string }[];
}

/** محطة في الخط الزمني بصفحة "من نحن" */
export interface Milestone {
    year: string;
    title: string;
    text: string;
}

/** عضو فريق */
export interface TeamMember {
    name: string;
    role: string;
    image: string;
}

/** خيارات فورم "اتصل بنا" */
export interface ContactOptions {
    areas: string[];
    budgets: string[];
    offices: { title: string; text: string }[];
    steps: { title: string; text: string }[];
    faq: { q: string; a: string }[];
}

/** حقل في فورم الريسورس العام */
export interface ResourceField {
    name: string;
    label: string;
    type: "text" | "number" | "password" | "date" | "textarea" | "select" | "toggle" | "image" | "gallery";
    hint?: string;
    required?: boolean;
    options?: { value: string; label: string }[];
}

/** سكيما الريسورس الجاية من الكنترولر */
export interface ResourceSchema {
    key: string;
    labels: { plural: string; singular: string };
    columns: Record<string, string>;
    fields: ResourceField[];
}
