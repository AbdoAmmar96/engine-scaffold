import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, Building2, Home, MapPin } from "lucide-react";
import PageHero from "@/Components/site/PageHero";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Area, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "المناطق",
        title: "المناطق التي نغطيها",
        desc: "لمن تناسب كل منطقة، وما المتاح فيها حاليًا — الأرقام محسوبة من المعروض فعليًا.",
        units: "وحدة",
        projects: "مشروع",
        view: "عرض المنطقة",
        empty: "لا توجد مناطق مضافة بعد.",
    },
    en: {
        crumb: "Areas",
        title: "The areas we cover",
        desc: "Who each area suits and what is currently listed in it — counts come from live inventory.",
        units: "units",
        projects: "projects",
        view: "View area",
        empty: "No areas added yet.",
    },
};

export default function Areas({ areas }: { areas: Area[] }) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    return (
        <SiteLayout>
            <PageHero bg="/images/demo/bg-props.jpg" crumb={t.crumb} title={t.title} desc={t.desc} />

            <section className="bg-bg px-4 py-12">
                <div className="mx-auto max-w-7xl">
                    {areas.length > 0 ? (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {areas.map((a, i) => (
                                <Reveal key={a.id} delay={i * 70}>
                                    <Link
                                        href={a.url}
                                        className="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-bg transition duration-200 hover:-translate-y-1 hover:border-primary/50 hover:shadow-[0_12px_30px_rgba(11,18,32,0.07)]"
                                    >
                                        <div className="relative h-48 overflow-hidden bg-surface">
                                            <img
                                                src={a.image}
                                                alt={a.name}
                                                loading="lazy"
                                                className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            />
                                            <div className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-bg-dark/85 to-transparent p-4">
                                                <h2 className="flex items-center gap-2 text-lg font-extrabold text-white">
                                                    <MapPin size={16} className="text-primary" />
                                                    {a.name}
                                                </h2>
                                            </div>
                                        </div>

                                        <div className="flex flex-1 flex-col gap-3 p-5">
                                            {a.note && (
                                                <p className="line-clamp-2 text-[13px] leading-[1.9] text-muted">{a.note}</p>
                                            )}

                                            <div className="flex flex-wrap items-center gap-4 text-[12px] font-bold text-muted">
                                                <span className="flex items-center gap-1.5">
                                                    <Home size={13} className="text-primary" />
                                                    <span dir="ltr">{a.properties ?? 0}</span>
                                                    {t.units}
                                                </span>
                                                <span className="flex items-center gap-1.5">
                                                    <Building2 size={13} className="text-primary" />
                                                    <span dir="ltr">{a.compounds ?? 0}</span>
                                                    {t.projects}
                                                </span>
                                            </div>

                                            <span className="mt-auto flex items-center gap-2 pt-1 text-[13px] font-extrabold text-secondary transition group-hover:text-primary">
                                                {t.view}
                                                <ArrowLeft size={14} className="text-primary ltr:rotate-180" />
                                            </span>
                                        </div>
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
