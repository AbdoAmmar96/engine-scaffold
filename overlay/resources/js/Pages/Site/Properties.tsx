import { Link, usePage } from "@inertiajs/react";
import { Building2, Home, LayoutGrid, SearchX } from "lucide-react";
import ActiveFilters, { type SearchFilters } from "@/Components/site/ActiveFilters";
import PageHero from "@/Components/site/PageHero";
import PropertyCard from "@/Components/site/PropertyCard";
import PropertyFilters from "@/Components/site/PropertyFilters";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Property, SearchOptions, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "عقارات",
        title: "عقارات للبيع والإيجار",
        titles: { commercial: "عقارات تجارية", residential: "عقارات سكنية" } as Record<string, string>,
        results: (n: number) => (n === 1 ? "وحدة واحدة مطابقة" : `${n} وحدة مطابقة`),
        section: "القسم",
        allSections: "الكل",
        residential: "سكني",
        commercial: "تجاري",
        emptyTitle: "مفيش نتائج بالفلاتر دي",
        emptyText: "جرّب توسّع نطاق السعر أو تشيل فلتر أو اتنين.",
    },
    en: {
        crumb: "Properties",
        title: "Properties for sale and rent",
        titles: { commercial: "Commercial properties", residential: "Residential properties" } as Record<string, string>,
        results: (n: number) => (n === 1 ? "1 matching unit" : `${n} matching units`),
        section: "Section",
        allSections: "All",
        residential: "Residential",
        commercial: "Commercial",
        emptyTitle: "No results with these filters",
        emptyText: "Try widening the price range or removing a filter or two.",
    },
};

export default function Properties({
    properties,
    filters,
    category,
    options,
}: {
    properties: Property[];
    filters: SearchFilters;
    /** جاي من الرابط: /properties/commercial — null يعني كل الأقسام */
    category: string | null;
    options: SearchOptions;
}) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const t = copy[locale] ?? copy.ar;
    const wa = settings.contact?.whatsapp;

    const path = category ? `/properties/${category}` : "/properties";

    // القسم لينك مش زرار: كل قسم صفحة مستقلة بميتا خاصة بيها
    const sectionTab = (key: string | null, text: string, Icon: typeof Home) => (
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
            {text}
        </Link>
    );

    return (
        <SiteLayout>
            <PageHero
                bg={category === "commercial" ? "/images/demo/bg-comps.jpg" : "/images/demo/bg-props.jpg"}
                crumb={t.crumb}
                title={(category && t.titles[category]) || t.title}
                desc={t.results(properties.length)}
            />

            <section className="bg-bg px-4 py-10">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-4 flex flex-wrap items-center gap-2">
                        <span className="text-xs font-extrabold text-secondary">{t.section}</span>
                        {sectionTab(null, t.allSections, LayoutGrid)}
                        {sectionTab("residential", t.residential, Home)}
                        {sectionTab("commercial", t.commercial, Building2)}
                    </div>

                    <ActiveFilters filters={filters} path={path} />

                    <PropertyFilters filters={filters} options={options} path={path} />

                    {properties.length > 0 ? (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {properties.map((p, i) => (
                                <Reveal key={p.id} delay={Math.min(i, 6) * 70}>
                                    <PropertyCard p={p} ar={ar} wa={wa} />
                                </Reveal>
                            ))}
                        </div>
                    ) : (
                        <div className="flex flex-col items-center rounded-3xl border border-gray-100 bg-surface px-6 py-16 text-center">
                            <span className="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                                <SearchX size={26} />
                            </span>
                            <h2 className="mt-4 text-xl font-extrabold text-secondary">{t.emptyTitle}</h2>
                            <p className="mt-2 text-sm text-muted">{t.emptyText}</p>
                        </div>
                    )}
                </div>
            </section>
        </SiteLayout>
    );
}
