import { router, usePage } from "@inertiajs/react";
import { X } from "lucide-react";
import type { SharedProps } from "@/lib/types";

export type SearchFilters = Record<string, string | number>;

const labels = {
    ar: {
        keys: {
            q: "بحث", type: "النوع", location: "المنطقة", purpose: "الغرض", finishing: "التشطيب",
            developer: "المطوّر", compound: "المشروع", sort: "الترتيب",
            price_min: "سعر من", price_max: "سعر إلى", area_min: "مساحة من", area_max: "مساحة إلى",
            beds: "غرف", baths: "حمامات", down_max: "أقصى مقدم", monthly_max: "أقصى قسط",
            years_max: "أقصى سنوات", delivery: "تسليم قبل",
            featured: "مميّزة", garden: "حديقة", roof: "روف", dressing: "غرفة ملابس",
        } as Record<string, string>,
        sale: "بيع",
        rent: "إيجار",
        yes: "نعم",
        clear: "امسح الكل",
        title: "نتائج البحث عن",
    },
    en: {
        keys: {
            q: "Search", type: "Type", location: "Area", purpose: "Purpose", finishing: "Finishing",
            developer: "Developer", compound: "Project", sort: "Sort",
            price_min: "Price from", price_max: "Price to", area_min: "Size from", area_max: "Size to",
            beds: "Beds", baths: "Baths", down_max: "Max down", monthly_max: "Max instalment",
            years_max: "Max years", delivery: "Delivered before",
            featured: "Featured", garden: "Garden", roof: "Roof", dressing: "Dressing room",
        } as Record<string, string>,
        sale: "Sale",
        rent: "Rent",
        yes: "Yes",
        clear: "Clear all",
        title: "Results for",
    },
};

const FLAGS = ["featured", "garden", "roof", "dressing"];

/**
 * شريط الفلاتر الشغّالة — بيبان بس لما يكون في فلتر، وكل واحد بيتشال لوحده
 * من غير ما يمسح الباقي.
 */
export default function ActiveFilters({
    filters,
    path,
    locked = [],
}: {
    filters: SearchFilters;
    path: string;
    /** فلاتر مثبّتة من الصفحة نفسها (صفحة هبوط) — بتتعرض من غير زرار شيل */
    locked?: string[];
}) {
    const { locale } = usePage<SharedProps>().props;
    const t = labels[locale] ?? labels.ar;

    // المقفولة مش بتتعرض: شيلها مش هيعمل حاجة — السيرفر بيرجّعها في كل الأحوال
    const active = Object.entries(filters).filter(
        ([key, v]) => v !== "" && v !== 0 && t.keys[key] && !locked.includes(key),
    );

    if (active.length === 0) return null;

    const go = (next: SearchFilters) => {
        const params = Object.fromEntries(Object.entries(next).filter(([, v]) => v !== "" && v !== 0));
        router.get(`/${locale}${path}`, params, { preserveScroll: true, preserveState: true, replace: true });
    };

    const show = (key: string, value: string | number) => {
        if (key === "purpose") return value === "rent" ? t.rent : t.sale;
        if (FLAGS.includes(key)) return t.yes;

        return String(value);
    };

    return (
        <div className="mb-5 flex flex-wrap items-center gap-2 rounded-2xl border border-primary/25 bg-primary/5 px-5 py-3.5">
            <span className="text-xs font-extrabold text-secondary">{t.title}:</span>

            {active.map(([key, value]) => (
                <button
                    key={key}
                    type="button"
                    onClick={() => go({ ...filters, [key]: "" })}
                    className="group flex items-center gap-1.5 rounded-full bg-bg px-3 py-1.5 text-[12px] font-extrabold text-secondary shadow-sm transition hover:text-danger"
                >
                    <span className="text-muted">{t.keys[key]}:</span>
                    <span dir="auto">{show(key, value)}</span>
                    <X size={12} className="text-muted transition group-hover:text-danger" />
                </button>
            ))}

            <button
                type="button"
                onClick={() => go({})}
                className="ms-auto text-[12px] font-extrabold text-muted underline-offset-4 transition hover:text-danger hover:underline"
            >
                {t.clear}
            </button>
        </div>
    );
}
