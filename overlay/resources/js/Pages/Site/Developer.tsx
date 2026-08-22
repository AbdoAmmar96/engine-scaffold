import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, Building2, CalendarDays, Globe, Home, MapPin } from "lucide-react";
import type { ReactNode } from "react";
import CompoundCard from "@/Components/site/CompoundCard";
import DeveloperLogo from "@/Components/site/DeveloperLogo";
import LeadForm from "@/Components/site/LeadForm";
import PageHero from "@/Components/site/PageHero";
import PropertyCard from "@/Components/site/PropertyCard";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Compound, DeveloperDetail, Property, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "المطوّرون",
        back: "كل المطوّرين",
        projects: "مشروع",
        units: "وحدة",
        areas: "منطقة",
        founded: "سنة التأسيس",
        hq: "المقر",
        site: "الموقع الرسمي",
        about: "عن المطوّر",
        projectsTitle: "مشاريع المطوّر",
        noProjects: "مفيش مشاريع معروضة للمطوّر ده دلوقتي.",
        unitsTitle: "وحدات متاحة",
        allUnits: "شوف كل الوحدات",
        formTitle: "استفسر عن مشاريع المطوّر",
        formNote: "قوللنا المنطقة والميزانية، وهنرشّح لك من مشاريعه اللي يناسبك.",
    },
    en: {
        crumb: "Developers",
        back: "All developers",
        projects: "projects",
        units: "units",
        areas: "areas",
        founded: "Founded",
        hq: "Headquarters",
        site: "Official website",
        about: "About the developer",
        projectsTitle: "Developer projects",
        noProjects: "No projects listed for this developer right now.",
        unitsTitle: "Available units",
        allUnits: "See all units",
        formTitle: "Ask about this developer's projects",
        formNote: "Tell us your area and budget and we'll shortlist what fits from their projects.",
    },
};

export default function DeveloperPage({
    developer,
    compounds,
    units,
}: {
    developer: DeveloperDetail;
    compounds: Compound[];
    units: Property[];
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

    const meta = (icon: ReactNode, label: string, value: ReactNode) => (
        <div className="flex items-start gap-3 rounded-xl bg-surface p-4">
            <span className="mt-0.5 shrink-0 text-primary">{icon}</span>
            <span className="flex min-w-0 flex-col gap-1">
                <span className="text-[11px] font-bold text-muted">{label}</span>
                <span className="text-sm font-extrabold text-secondary">{value}</span>
            </span>
        </div>
    );

    return (
        <SiteLayout>
            <PageHero
                bg={developer.cover}
                crumb={t.crumb}
                crumbHref={`/${locale}/developers`}
                title={developer.name}
                desc={developer.headquarters || undefined}
            >
                <div className="flex flex-wrap items-center gap-4">
                    <span className="rounded-2xl bg-white p-1">
                        <DeveloperLogo name={developer.name} logo={developer.logo} size={52} />
                    </span>

                    <div className="flex flex-wrap items-center gap-2.5">
                        {stat(developer.compounds, t.projects)}
                        {developer.units > 0 && stat(developer.units, t.units)}
                        {developer.areas > 0 && stat(developer.areas, t.areas)}
                    </div>
                </div>
            </PageHero>

            <section className="bg-bg px-4 py-12">
                <div className="mx-auto flex max-w-7xl flex-col gap-10">
                    {(developer.founded || developer.headquarters || developer.website) && (
                        <Reveal>
                            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                {developer.founded &&
                                    meta(<CalendarDays size={17} />, t.founded, <span dir="ltr">{developer.founded}</span>)}
                                {developer.headquarters && meta(<MapPin size={17} />, t.hq, developer.headquarters)}
                                {developer.website &&
                                    meta(
                                        <Globe size={17} />,
                                        t.site,
                                        <a
                                            href={developer.website}
                                            target="_blank"
                                            rel="noreferrer nofollow"
                                            dir="ltr"
                                            className="break-all text-primary transition hover:underline"
                                        >
                                            {developer.website.replace(/^https?:\/\//, "")}
                                        </a>,
                                    )}
                            </div>
                        </Reveal>
                    )}

                    {developer.about && (
                        <Reveal>
                            <div>
                                <h2 className="mb-3 text-xl font-extrabold text-secondary">{t.about}</h2>
                                <div className="flex flex-col gap-4">
                                    {developer.about
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

                    {units.length > 0 && (
                        <div>
                            <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
                                <h2 className="flex items-center gap-2 text-xl font-extrabold text-secondary">
                                    <Home size={19} className="text-primary" />
                                    {t.unitsTitle}
                                </h2>
                                <Link
                                    href={`/${locale}/properties?q=${encodeURIComponent(developer.name)}`}
                                    className="flex items-center gap-2 text-[13px] font-extrabold text-secondary transition hover:text-primary"
                                >
                                    {t.allUnits}
                                    <ArrowLeft size={14} className="text-primary ltr:rotate-180" />
                                </Link>
                            </div>

                            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                                {units.map((p, i) => (
                                    <Reveal key={p.id} delay={i * 70}>
                                        <PropertyCard p={p} ar={ar} wa={wa} />
                                    </Reveal>
                                ))}
                            </div>
                        </div>
                    )}

                    <div id="lead" className="scroll-mt-24">
                        <h2 className="mb-1 text-xl font-extrabold text-secondary">{t.formTitle}</h2>
                        <p className="mb-4 text-[13px] text-muted">{t.formNote}</p>
                        <LeadForm source="compound" subject={developer.name} />
                    </div>

                    <Link
                        href={`/${locale}/developers`}
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
