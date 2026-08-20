import { router, usePage } from "@inertiajs/react";
import { X } from "lucide-react";
import type { SharedProps } from "@/lib/types";

export interface SearchFilters {
    q?: string;
    type?: string;
    location?: string;
    purpose?: string;
}

const labels = {
    ar: { q: "بحث", type: "النوع", location: "المنطقة", purpose: "الغرض", sale: "بيع", rent: "إيجار", clear: "امسح الكل", title: "نتايج البحث عن" },
    en: { q: "Search", type: "Type", location: "Area", purpose: "Purpose", sale: "Sale", rent: "Rent", clear: "Clear all", title: "Results for" },
};

/**
 * شريط الفلاتر الجايّة من فورم البحث في الهيرو — بيبان بس لما يكون في فلتر شغّال،
 * وكل فلتر بيتشال لوحده من غير ما يمسح الباقي.
 */
export default function ActiveFilters({ filters, path }: { filters: SearchFilters; path: string }) {
    const { locale } = usePage<SharedProps>().props;
    const t = labels[locale] ?? labels.ar;

    const active = (Object.entries(filters) as [keyof SearchFilters, string][]).filter(([, v]) => v);

    if (active.length === 0) return null;

    const go = (next: SearchFilters) => {
        const params = Object.fromEntries(Object.entries(next).filter(([, v]) => v));
        router.get(`/${locale}${path}`, params, { preserveScroll: true, preserveState: true });
    };

    const show = (key: keyof SearchFilters, value: string) =>
        key === "purpose" ? (value === "rent" ? t.rent : t.sale) : value;

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
                    <span className="text-muted">{t[key]}:</span>
                    {show(key, value)}
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
