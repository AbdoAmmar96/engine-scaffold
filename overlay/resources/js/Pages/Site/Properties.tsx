import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, Building2, Home, LayoutGrid, SearchX } from "lucide-react";
import ActiveFilters, { type SearchFilters } from "@/Components/site/ActiveFilters";
import AdStrip, { type Ad } from "@/Components/site/AdStrip";
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
        emptyTitle: "لا توجد نتائج بهذه الفلاتر",
        emptyText: "جرّب توسيع نطاق السعر أو إزالة فلتر أو اثنين.",
        related: "صفحات قريبة",
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
        related: "Related pages",
    },
};

/** صفحة هبوط برمجية — بتيجي من /properties/{slug} لما الرابط تركيبة مش وحدة */
interface Landing {
    slug: string;
    title: string;
    intro: string;
    /** أبعاد الصفحة المثبّتة: type · purpose · location */
    locked: string[];
    related: { label: string; url: string; count: number }[];
}

export default function Properties({
    properties,
    filters,
    category,
    options,
    landing = null,
    ads = [],
}: {
    properties: Property[];
    filters: SearchFilters;
    /** جاي من الرابط: /properties/commercial — null يعني كل الأقسام */
    category: string | null;
    options: SearchOptions;
    landing?: Landing | null;
    ads?: Ad[];
}) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const t = copy[locale] ?? copy.ar;
    const wa = settings.contact?.whatsapp;

    // صفحة الهبوط ليها مسارها الخاص عشان الفلاتر تفضل جواها
    const path = landing
        ? `/properties/${landing.slug}`
        : category
          ? `/properties/${category}`
          : "/properties";

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
                title={landing?.title || (category && t.titles[category]) || t.title}
                desc={t.results(properties.length)}
            />

            <section className="bg-bg px-4 py-10">
                <div className="mx-auto max-w-7xl">
                    {landing ? (
                        <p className="mb-6 max-w-3xl text-[15px] leading-8 text-muted">{landing.intro}</p>
                    ) : (
                        <div className="mb-4 flex flex-wrap items-center gap-2">
                            <span className="text-xs font-extrabold text-secondary">{t.section}</span>
                            {sectionTab(null, t.allSections, LayoutGrid)}
                            {sectionTab("residential", t.residential, Home)}
                            {sectionTab("commercial", t.commercial, Building2)}
                        </div>
                    )}

                    <ActiveFilters filters={filters} path={path} locked={landing?.locked} />

                    <PropertyFilters filters={filters} options={options} path={path} locked={landing?.locked} />

                    {ads.length > 0 && (
                        <div className="mb-6">
                            <AdStrip ads={ads} compact />
                        </div>
                    )}

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

                    {landing && landing.related.length > 0 && (
                        <nav className="mt-10 rounded-3xl border border-gray-100 bg-bg p-6">
                            <h2 className="mb-4 text-sm font-extrabold text-secondary">{t.related}</h2>
                            <ul className="flex flex-wrap gap-2">
                                {landing.related.map((r) => (
                                    <li key={r.url}>
                                        <Link
                                            href={r.url}
                                            className="flex items-center gap-2 rounded-full border border-gray-200 bg-surface px-4 py-2 text-[13px] font-extrabold text-secondary transition hover:border-primary hover:text-primary"
                                        >
                                            <ArrowLeft size={13} className="rtl:rotate-180" />
                                            {r.label}
                                            <span className="text-[11px] font-bold text-muted" dir="auto">
                                                {r.count}
                                            </span>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </nav>
                    )}
                </div>
            </section>
        </SiteLayout>
    );
}
