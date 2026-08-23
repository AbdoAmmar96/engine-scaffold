import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, Building2, Home, Search } from "lucide-react";
import { useMemo, useState } from "react";
import DeveloperLogo from "@/Components/site/DeveloperLogo";
import PageHero from "@/Components/site/PageHero";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { DeveloperCard, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "المطوّرون",
        title: "المطوّرون العقاريون",
        desc: "المطوّرون الذين نعمل معهم، ومشاريعهم، والمناطق التي يبنون فيها.",
        search: "ابحث باسم المطوّر…",
        projects: "مشروع",
        units: "وحدة",
        view: "عرض المطوّر",
        empty: "لا يوجد مطوّر بهذا الاسم.",
        count: (n: number) => `${n} مطوّر`,
    },
    en: {
        crumb: "Developers",
        title: "Real-estate developers",
        desc: "The developers we work with, their projects, and the areas they build in.",
        search: "Search by developer name…",
        projects: "projects",
        units: "units",
        view: "View developer",
        empty: "No developer matches that name.",
        count: (n: number) => `${n} developers`,
    },
};

export default function Developers({ developers }: { developers: DeveloperCard[] }) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;
    const [q, setQ] = useState("");

    const shown = useMemo(() => {
        const needle = q.trim().toLowerCase();

        return needle ? developers.filter((d) => d.name.toLowerCase().includes(needle)) : developers;
    }, [developers, q]);

    return (
        <SiteLayout>
            <PageHero bg="/images/demo/bg-comps.jpg" crumb={t.crumb} title={t.title} desc={t.desc} />

            <section className="bg-bg px-4 py-10">
                <div className="mx-auto max-w-7xl">
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                        <p className="text-sm font-bold text-muted">{t.count(shown.length)}</p>

                        <label className="relative w-full max-w-xs">
                            <Search size={16} className="absolute inset-y-0 start-3 my-auto text-muted" />
                            <input
                                value={q}
                                onChange={(e) => setQ(e.target.value)}
                                placeholder={t.search}
                                aria-label={t.search}
                                className="w-full rounded-xl border border-gray-200 bg-bg py-3 pe-4 ps-10 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20"
                            />
                        </label>
                    </div>

                    {shown.length > 0 ? (
                        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                            {shown.map((d, i) => (
                                <Reveal key={d.id} delay={i * 60}>
                                    <Link
                                        href={d.url}
                                        className="group flex h-full flex-col gap-4 rounded-2xl border border-gray-100 bg-bg p-6 transition duration-200 hover:-translate-y-1 hover:border-primary/50 hover:shadow-[0_12px_30px_rgba(11,18,32,0.07)]"
                                    >
                                        <div className="flex items-center gap-4">
                                            <DeveloperLogo name={d.name} logo={d.logo} />
                                            <div className="min-w-0">
                                                <h2 className="truncate text-[17px] font-extrabold text-secondary transition group-hover:text-primary">
                                                    {d.name}
                                                </h2>
                                                <div className="mt-1.5 flex flex-wrap items-center gap-3 text-[11px] font-bold text-muted">
                                                    <span className="flex items-center gap-1.5">
                                                        <Building2 size={12} className="text-primary" />
                                                        <span dir="ltr">{d.compounds}</span>
                                                        {t.projects}
                                                    </span>
                                                    {typeof d.units === "number" && d.units > 0 && (
                                                        <span className="flex items-center gap-1.5">
                                                            <Home size={12} className="text-primary" />
                                                            <span dir="ltr">{d.units}</span>
                                                            {t.units}
                                                        </span>
                                                    )}
                                                </div>
                                            </div>
                                        </div>

                                        {d.about && (
                                            <p className="line-clamp-3 text-[13px] leading-[1.9] text-muted">{d.about}</p>
                                        )}

                                        <span className="mt-auto flex items-center gap-2 pt-1 text-[13px] font-extrabold text-secondary transition group-hover:text-primary">
                                            {t.view}
                                            <ArrowLeft size={14} className="text-primary ltr:rotate-180" />
                                        </span>
                                    </Link>
                                </Reveal>
                            ))}
                        </div>
                    ) : (
                        <p className="rounded-2xl border border-gray-100 bg-surface px-6 py-16 text-center text-sm text-muted">
                            {t.empty}
                        </p>
                    )}
                </div>
            </section>
        </SiteLayout>
    );
}
