import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, Briefcase, Building2, Home } from "lucide-react";
import CompoundCard from "@/Components/site/CompoundCard";
import LeadForm from "@/Components/site/LeadForm";
import PageHero from "@/Components/site/PageHero";
import PropertyCard from "@/Components/site/PropertyCard";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { AreaDetail, Compound, Property, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "المناطق",
        back: "كل المناطق",
        units: "وحدة",
        projects: "مشروع",
        developers: "مطوّر",
        about: "عن المنطقة",
        projectsTitle: "مشاريع في المنطقة",
        noProjects: "لا توجد مشاريع معروضة في هذه المنطقة حاليًا.",
        unitsTitle: "وحدات في المنطقة",
        noUnits: "لا توجد وحدات معروضة في هذه المنطقة حاليًا.",
        allUnits: "عرض كل الوحدات",
        formTitle: "ابحث لي عن وحدة في هذه المنطقة",
        formNote: "أخبرنا بالميزانية ونوع الوحدة، وسنعود إليك بما هو متاح فعلًا.",
    },
    en: {
        crumb: "Areas",
        back: "All areas",
        units: "units",
        projects: "projects",
        developers: "developers",
        about: "About the area",
        projectsTitle: "Projects in this area",
        noProjects: "No projects listed in this area right now.",
        unitsTitle: "Units in this area",
        noUnits: "No units listed in this area right now.",
        allUnits: "See all units",
        formTitle: "Find me a unit in this area",
        formNote: "Tell us your budget and unit type, and we'll come back with what's actually available.",
    },
};

export default function AreaPage({
    area,
    compounds,
    properties,
}: {
    area: AreaDetail;
    compounds: Compound[];
    properties: Property[];
}) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const t = copy[locale] ?? copy.ar;
    const wa = settings.contact?.whatsapp;

    const stat = (value: number, label: string) => (
        <span className="flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-[12px] font-extrabold text-white/85">
            <span className="text-primary" dir="ltr">
                {value}
            </span>
            {label}
        </span>
    );

    // فلتر القائمة بيدوّر بالاسم، فالرابط بيوصّل لنتائج المنطقة
    const listingUrl = `/${locale}/properties?location=${encodeURIComponent(area.name)}`;

    return (
        <SiteLayout>
            <PageHero
                bg={area.cover}
                crumb={t.crumb}
                crumbHref={`/${locale}/areas`}
                title={area.name}
                desc={area.note || undefined}
            >
                <div className="flex flex-wrap items-center gap-2.5">
                    {stat(area.properties, t.units)}
                    {stat(area.compounds, t.projects)}
                    {area.developers > 0 && stat(area.developers, t.developers)}
                </div>
            </PageHero>

            <section className="bg-bg px-4 py-12">
                <div className="mx-auto flex max-w-7xl flex-col gap-10">
                    {area.about && (
                        <Reveal>
                            <div>
                                <h2 className="mb-3 text-xl font-extrabold text-secondary">{t.about}</h2>
                                <div className="flex flex-col gap-4">
                                    {area.about
                                        .split("\n")
                                        .map((line) => line.trim())
                                        .filter(Boolean)
                                        .map((line, i) => (
                                            <p key={i} className="text-[15px] leading-[2.1] text-text">
                                                {line}
                                            </p>
                                        ))}
                                </div>
                            </div>
                        </Reveal>
                    )}

                    <div>
                        <h2 className="mb-5 flex items-center gap-2 text-xl font-extrabold text-secondary">
                            <Building2 size={19} className="text-primary" />
                            {t.projectsTitle}
                        </h2>

                        {compounds.length > 0 ? (
                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {compounds.map((c, i) => (
                                    <Reveal key={c.id} delay={i * 70}>
                                        <CompoundCard c={c} ar={ar} />
                                    </Reveal>
                                ))}
                            </div>
                        ) : (
                            <p className="rounded-2xl border border-gray-100 bg-surface px-6 py-12 text-center text-sm text-muted">
                                {t.noProjects}
                            </p>
                        )}
                    </div>

                    <div>
                        <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
                            <h2 className="flex items-center gap-2 text-xl font-extrabold text-secondary">
                                <Home size={19} className="text-primary" />
                                {t.unitsTitle}
                            </h2>
                            {properties.length > 0 && (
                                <Link
                                    href={listingUrl}
                                    className="flex items-center gap-2 text-[13px] font-extrabold text-secondary transition hover:text-primary"
                                >
                                    {t.allUnits}
                                    <ArrowLeft size={14} className="text-primary ltr:rotate-180" />
                                </Link>
                            )}
                        </div>

                        {properties.length > 0 ? (
                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {properties.map((p, i) => (
                                    <Reveal key={p.id} delay={i * 70}>
                                        <PropertyCard p={p} ar={ar} wa={wa} />
                                    </Reveal>
                                ))}
                            </div>
                        ) : (
                            <p className="rounded-2xl border border-gray-100 bg-surface px-6 py-12 text-center text-sm text-muted">
                                {t.noUnits}
                            </p>
                        )}
                    </div>

                    <div id="lead" className="scroll-mt-24">
                        <h2 className="mb-1 flex items-center gap-2 text-xl font-extrabold text-secondary">
                            <Briefcase size={19} className="text-primary" />
                            {t.formTitle}
                        </h2>
                        <p className="mb-4 text-[13px] text-muted">{t.formNote}</p>
                        <LeadForm source="property" subject={area.name} />
                    </div>

                    <Link
                        href={`/${locale}/areas`}
                        className="flex w-fit items-center gap-2 text-[13px] font-extrabold text-secondary transition hover:text-primary"
                    >
                        <ArrowLeft size={15} className="text-primary ltr:rotate-180" />
                        {t.back}
                    </Link>
                </div>
            </section>
        </SiteLayout>
    );
}
