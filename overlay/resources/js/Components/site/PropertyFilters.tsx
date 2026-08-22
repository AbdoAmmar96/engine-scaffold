import { router, usePage } from "@inertiajs/react";
import { RotateCcw, SlidersHorizontal } from "lucide-react";
import { useState } from "react";
import type { SearchOptions, SharedProps } from "@/lib/types";

export type Filters = Record<string, string | number>;

const copy = {
    ar: {
        advanced: "فلاتر متقدمة",
        search: "ابحث بالكود أو الاسم أو المنطقة…",
        area: "المنطقة",
        type: "النوع",
        purpose: "الغرض",
        sale: "بيع",
        rent: "إيجار",
        all: "الكل",
        price: "السعر (جنيه)",
        size: "المساحة (م²)",
        from: "من",
        to: "إلى",
        beds: "غرف النوم (أو أكتر)",
        baths: "الحمامات (أو أكتر)",
        finishing: "التشطيب",
        developer: "المطوّر",
        compound: "المشروع",
        downMax: "أقصى مقدم",
        monthlyMax: "أقصى قسط شهري",
        yearsMax: "أقصى سنوات تقسيط",
        delivery: "التسليم قبل سنة",
        featured: "المميّزة فقط",
        garden: "حديقة",
        roof: "روف",
        dressing: "غرفة ملابس",
        apply: "طبّق الفلاتر",
        reset: "امسح الكل",
        sort: "ترتيب",
    },
    en: {
        advanced: "Advanced filters",
        search: "Search by code, name or area…",
        area: "Area",
        type: "Type",
        purpose: "Purpose",
        sale: "Sale",
        rent: "Rent",
        all: "All",
        price: "Price (EGP)",
        size: "Size (m²)",
        from: "From",
        to: "To",
        beds: "Bedrooms (or more)",
        baths: "Bathrooms (or more)",
        finishing: "Finishing",
        developer: "Developer",
        compound: "Project",
        downMax: "Max down payment",
        monthlyMax: "Max monthly instalment",
        yearsMax: "Max instalment years",
        delivery: "Delivered before",
        featured: "Featured only",
        garden: "Garden",
        roof: "Roof",
        dressing: "Dressing room",
        apply: "Apply filters",
        reset: "Clear all",
        sort: "Sort",
    },
};

const field =
    "w-full rounded-xl border border-gray-200 bg-bg px-3.5 py-2.5 text-[13px] outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20";
const label = "mb-1.5 block text-[11px] font-extrabold text-secondary";

/**
 * لوحة فلاتر العقارات. كل فلتر بيروح للسيرفر في الرابط —
 * مش فلترة في المتصفح، عشان النتيجة تفضل مشاركة وقابلة للفهرسة.
 */
export default function PropertyFilters({
    filters,
    options,
    path,
    locked = [],
}: {
    filters: Filters;
    options: SearchOptions;
    /** مسار الصفحة من غير اللغة — /properties أو /properties/commercial */
    path: string;
    /**
     * فلاتر الصفحة الثابتة (صفحة هبوط): بتتخفي من اللوحة عشان الزائر
     * يضيّق النتيجة مش يغيّر موضوع الصفحة. السيرفر بيفرضها برضه.
     */
    locked?: string[];
}) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const [draft, setDraft] = useState<Filters>(filters);
    // الفلاتر المتقدمة بتفتح لوحدها لو فيه واحدة شغّالة من قبل
    const [open, setOpen] = useState(
        ["price_min", "price_max", "area_min", "area_max", "beds", "baths", "finishing", "developer", "compound", "down_max", "monthly_max", "years_max", "delivery", "featured", "garden", "roof", "dressing"].some(
            (k) => filters[k],
        ),
    );

    const set = (key: string, value: string | number) => setDraft((d) => ({ ...d, [key]: value }));

    const shown = (key: string) => !locked.includes(key);

    const go = (next: Filters) => {
        const clean = Object.fromEntries(Object.entries(next).filter(([, v]) => v !== "" && v !== 0));
        router.get(`/${locale}${path}`, clean, { preserveScroll: true, preserveState: true, replace: true });
    };

    const val = (key: string) => String(draft[key] ?? "");

    const text = (key: string, placeholder: string) => (
        <input value={val(key)} onChange={(e) => set(key, e.target.value)} placeholder={placeholder} className={field} />
    );

    const number = (key: string, placeholder: string) => (
        <input
            type="number"
            min={0}
            dir="ltr"
            value={val(key)}
            onChange={(e) => set(key, e.target.value)}
            placeholder={placeholder}
            className={field}
        />
    );

    const select = (key: string, items: { value: string; label: string }[], anyLabel: string) => (
        <select value={val(key)} onChange={(e) => set(key, e.target.value)} className={field}>
            <option value="">{anyLabel}</option>
            {items.map((o) => (
                <option key={o.value} value={o.value}>
                    {o.label}
                </option>
            ))}
        </select>
    );

    const check = (key: string, text: string) => (
        <label className="flex items-center gap-2 text-[13px] font-bold text-secondary">
            <input
                type="checkbox"
                checked={Boolean(draft[key])}
                onChange={(e) => set(key, e.target.checked ? "1" : "")}
                className="h-4 w-4 accent-[var(--primary)]"
            />
            {text}
        </label>
    );

    const plain = (list: string[]) => list.map((v) => ({ value: v, label: v }));

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                go(draft);
            }}
            className="mb-6 rounded-3xl border border-gray-100 bg-bg p-5 shadow-[0_4px_18px_rgba(11,18,32,0.04)]"
        >
            {/* ---------- الصف السريع ---------- */}
            <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                <div className="lg:col-span-2">
                    <span className={label}>{t.search}</span>
                    {text("q", t.search)}
                </div>
                {shown("location") && (
                    <div>
                        <span className={label}>{t.area}</span>
                        {select("location", plain(options.locations), t.all)}
                    </div>
                )}
                {shown("type") && (
                    <div>
                        <span className={label}>{t.type}</span>
                        {select("type", plain(options.types), t.all)}
                    </div>
                )}
            </div>

            <div className="mt-3 grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                {shown("purpose") && (
                    <div>
                        <span className={label}>{t.purpose}</span>
                        {select("purpose", [{ value: "sale", label: t.sale }, { value: "rent", label: t.rent }], t.all)}
                    </div>
                )}
                <div>
                    <span className={label}>{t.sort}</span>
                    {select("sort", options.sorts, options.sorts[0]?.label ?? t.all)}
                </div>
            </div>

            {/* ---------- المتقدمة ---------- */}
            {open && (
                <div className="mt-5 grid gap-3 border-t border-gray-100 pt-5 md:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <span className={label}>{`${t.price} · ${t.from}`}</span>
                        {number("price_min", String(options.bounds.priceMin || ""))}
                    </div>
                    <div>
                        <span className={label}>{`${t.price} · ${t.to}`}</span>
                        {number("price_max", String(options.bounds.priceMax || ""))}
                    </div>
                    <div>
                        <span className={label}>{`${t.size} · ${t.from}`}</span>
                        {number("area_min", String(options.bounds.areaMin || ""))}
                    </div>
                    <div>
                        <span className={label}>{`${t.size} · ${t.to}`}</span>
                        {number("area_max", String(options.bounds.areaMax || ""))}
                    </div>

                    <div>
                        <span className={label}>{t.beds}</span>
                        {number("beds", "0")}
                    </div>
                    <div>
                        <span className={label}>{t.baths}</span>
                        {number("baths", "0")}
                    </div>
                    <div>
                        <span className={label}>{t.finishing}</span>
                        {select("finishing", options.finishing, t.all)}
                    </div>
                    <div>
                        <span className={label}>{t.delivery}</span>
                        {number("delivery", "2030")}
                    </div>

                    <div>
                        <span className={label}>{t.developer}</span>
                        {select("developer", options.developers, t.all)}
                    </div>
                    <div>
                        <span className={label}>{t.compound}</span>
                        {select("compound", options.compounds, t.all)}
                    </div>
                    <div>
                        <span className={label}>{t.downMax}</span>
                        {number("down_max", "")}
                    </div>
                    <div>
                        <span className={label}>{t.monthlyMax}</span>
                        {number("monthly_max", "")}
                    </div>

                    <div>
                        <span className={label}>{t.yearsMax}</span>
                        {number("years_max", "")}
                    </div>

                    <div className="flex flex-wrap items-center gap-x-5 gap-y-2 md:col-span-2 lg:col-span-3">
                        {check("featured", t.featured)}
                        {check("garden", t.garden)}
                        {check("roof", t.roof)}
                        {check("dressing", t.dressing)}
                    </div>
                </div>
            )}

            {/* ---------- الأزرار ---------- */}
            <div className="mt-5 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-4">
                <button
                    type="submit"
                    className="rounded-brand bg-primary px-6 py-2.5 text-[13px] font-extrabold text-primary-fg transition hover:bg-primary-hover"
                >
                    {t.apply}
                </button>

                <button
                    type="button"
                    onClick={() => setOpen(!open)}
                    aria-expanded={open}
                    className="flex items-center gap-2 rounded-brand border border-gray-200 px-4 py-2.5 text-[13px] font-extrabold text-secondary transition hover:border-primary hover:text-primary"
                >
                    <SlidersHorizontal size={15} />
                    {t.advanced}
                </button>

                <button
                    type="button"
                    onClick={() => {
                        // الأبعاد المقفولة بتفضل — «امسح الكل» بيمسح تضييق الزائر بس
                        const base = Object.fromEntries(locked.map((k) => [k, filters[k] ?? ""]));
                        setDraft(base);
                        go(base);
                    }}
                    className="ms-auto flex items-center gap-2 text-[12px] font-extrabold text-muted transition hover:text-danger"
                >
                    <RotateCcw size={14} />
                    {t.reset}
                </button>
            </div>
        </form>
    );
}
