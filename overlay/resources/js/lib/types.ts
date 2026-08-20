export type SettingsGroups = Record<string, Record<string, string>>;

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    /** صلاحيات المستخدم — بتحدد اللي بيظهر في سايدبار اللوحة */
    can: string[];
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
    title: string;
    area: string;
    purpose: string;
    price: string;
    beds: number;
    baths: number;
    size: number;
    ref: string;
    image: string;
}

export interface Compound {
    id: number;
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

/** بطاقة منطقة في قسم "مناطق بنغطيها" بالرئيسية */
export interface Area {
    id: number;
    name: string;
    note: string;
    count: string;
    image: string;
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
    type: "text" | "number" | "password" | "date" | "textarea" | "select" | "toggle" | "image";
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
