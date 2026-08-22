import { Link, usePage } from "@inertiajs/react";
import { Building2, Home, LayoutGrid, SearchX } from "lucide-react";
import { useMemo, useState } from "react";
import ActiveFilters, { type SearchFilters } from "@/Components/site/ActiveFilters";
import PageHero from "@/Components/site/PageHero";
import PropertyCard from "@/Components/site/PropertyCard";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Property, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "عقارات",
        title: "عقارات للبيع والإيجار",
        titles: { commercial: "عقارات تجارية", residential: "عقارات سكنية" } as Record<string, string>,
        section: "القسم",
        allSections: "الكل",
        residential: "سكني",
        commercial: "تجاري",
        results: (n: number, total: number) =>
            n === total ? `${total} وحدة متاحة حاليًا بكل المناطق.` : `${n} وحدة من أصل ${total} مطابقة للفلاتر.`,
        purpose: "الغرض",
        area: "المنطقة",
        all: "الكل",
        emptyTitle: "مفيش نتائج بالفلاتر دي",
        emptyText: "جرّب توسّع نطاق المنطقة أو تغيّر الغرض.",
        reset: "امسح الفلاتر",
    },
    en: {
        crumb: "Properties",
        title: "Properties for sale and rent",
        titles: { commercial: "Commercial properties", residential: "Residential properties" } as Record<string, string>,
        section: "Section",
        allSections: "All",
        residential: "Residential",
        commercial: "Commercial",
        results: (n: number, total: number) =>
            n === total ? `${total} units currently available across all areas.` : `${n} of ${total} units match your filters.`,
        purpose: "Purpose",
        area: "Area",
        all: "All",
        emptyTitle: "No results with these filters",
        emptyText: "Try widening the area or changing the purpose.",
        reset: "Clear filters",
    },
};

export default function Properties({
    properties,
    filters,
    category,
}: {
    properties: Property[];
    filters: SearchFilters;
    /** جاي من الرابط: /properties/commercial — null يعني كل الأقسام */
    category: string | null;
}) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const t = copy[locale] ?? copy.ar;
    const wa = settings.contact?.whatsapp;

    const [purpose, setPurpose] = useState<string | null>(null);
    const [area, setArea] = useState<string | null>(null);

    const purposes = useMemo(() => [...new Set(properties.map((p) => p.purpose))], [properties]);
    const areas = useMemo(() => [...new Set(properties.map((p) => p.area))], [properties]);

    const filtered = properties.filter(
        (p) => (!purpose || p.purpose === purpose) && (!area || p.area === area),
    );

    const sectionTab = (key: string | null, label: string, Icon: typeof Home) => (
        <Link
            key={key ?? "all"}
            href={key ? `/${locale}/properties/${key}` : `/${locale}/properties`}
            className={`flex items-center gap-2 rounded-full px-4 py-2 text-[13px] font-extrabold transition ${
                category === key
                    ? "bg-secondary text-white"
                    : "border border-gray-200 bg-bg text-secondary hover:border-primary hover:text-primary"
            }`}
        >
            <Icon size={14} />
            {label}
        </Link>
    );

    const chip = (label: string, selected: boolean, onClick: () => void) => (
        <button
            key={label}
            type="button"
            onClick={onClick}
            className={`rounded-full px-4 py-2 text-[13px] font-extrabold transition ${
                selected
                    ? "bg-primary text-primary-fg"
                    : "border border-gray-200 bg-bg text-secondary hover:border-primary hover:text-primary"
            }`}
        >
            {label}
        </button>
    );

    return (
        <SiteLayout>
            <PageHero
                bg={category === "commercial" ? "/images/demo/bg-comps.jpg" : "/images/demo/bg-props.jpg"}
                crumb={t.crumb}
                title={(category && t.titles[category]) || t.title}
                desc={t.results(filtered.length, properties.length)}
            />

            <section className="bg-bg px-4 py-10">
                <div className="mx-auto max-w-7xl">
                    <ActiveFilters filters={filters} path="/properties" />

                    {/* ---------- تبويبات القسم ---------- */}
                    {/* لينكات مش أزرار: كل قسم رابط مستقل بميتا خاصة بيه */}
                    <div className="mb-4 flex flex-wrap items-center gap-2">
                        <span className="text-xs font-extrabold text-secondary">{t.section}</span>
                        {sectionTab(null, t.allSections, LayoutGrid)}
                        {sectionTab("residential", t.residential, Home)}
                        {sectionTab("commercial", t.commercial, Building2)}
                    </div>

                    {/* ---------- شريط الفلاتر ---------- */}
                    <div className="mb-6 flex flex-wrap items-center gap-x-7 gap-y-4 rounded-3xl border border-gray-100 bg-bg px-6 py-4 shadow-[0_4px_18px_rgba(11,18,32,0.04)]">
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="text-xs font-extrabold text-secondary">{t.purpose}</span>
                            {chip(t.all, purpose === null, () => setPurpose(null))}
                            {purposes.map((v) => chip(v, purpose === v, () => setPurpose(v)))}
                        </div>

                        <span className="hidden h-8 w-px bg-gray-200 md:block" aria-hidden />

                        <div className="flex flex-wrap items-center gap-2">
                            <span className="text-xs font-extrabold text-secondary">{t.area}</span>
                            {chip(t.all, area === null, () => setArea(null))}
                            {areas.map((v) => chip(v, area === v, () => setArea(v)))}
                        </div>
                    </div>

                    {/* ---------- النتائج ---------- */}
                    {filtered.length > 0 ? (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {filtered.map((p, i) => (
                                <Reveal key={p.id} delay={i * 80}>
                                    <PropertyCard p={p} ar={ar} wa={wa} />
                                </Reveal>
                            ))}
                        </div>
                    ) : (
                        <div className="flex flex-col items-center rounded-3xl border border-gray-100 bg-surface px-6 py-16 text-center">
                            <span className="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <SearchX size={26} />
                            </span>
                            <h3 className="mt-4 text-xl font-extrabold text-secondary">{t.emptyTitle}</h3>
                            <p className="mt-2 text-sm text-muted">{t.emptyText}</p>
                            <button
                                type="button"
                                onClick={() => {
                                    setPurpose(null);
                                    setArea(null);
                                }}
                                className="mt-6 rounded-brand bg-primary px-6 py-3 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover"
                            >
                                {t.reset}
                            </button>
                        </div>
                    )}
                </div>
            </section>
        </SiteLayout>
    );
}
