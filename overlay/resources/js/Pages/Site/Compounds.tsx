import { usePage } from "@inertiajs/react";
import { CalendarCheck, MapPin } from "lucide-react";
import ActiveFilters, { type SearchFilters } from "@/Components/site/ActiveFilters";
import PageHero from "@/Components/site/PageHero";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Compound, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "الكمبوندات",
        title: "الكمبوندات",
        desc: "مشاريع سكنية وساحلية بأنظمة سداد معلنة من المطوّر، مع تواريخ تسليم موثّقة في العقد.",
        from: "يبدأ من",
        down: "مقدم",
        plan: "تقسيط",
        delivery: "التسليم",
        wa: "استفسر واتساب",
        newTag: "إطلاق جديد",
    },
    en: {
        crumb: "Compounds",
        title: "Compounds",
        desc: "Residential and coastal projects with payment plans stated by the developer, and delivery dates documented in the contract.",
        from: "From",
        down: "Down",
        plan: "Plan",
        delivery: "Delivery",
        wa: "Ask on WhatsApp",
        newTag: "New launch",
    },
};

export default function Compounds({ compounds, filters }: { compounds: Compound[]; filters: SearchFilters }) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const t = copy[locale] ?? copy.ar;
    const wa = settings.contact?.whatsapp;

    const cell = (label: string, value: string, gold = false) => (
        <div className="flex flex-col items-center gap-1 px-2">
            <span className="text-[11px] font-bold text-muted">{label}</span>
            <span className={`text-sm font-extrabold ${gold ? "text-primary" : "text-secondary"}`} dir="ltr">
                {value}
            </span>
        </div>
    );

    return (
        <SiteLayout>
            <PageHero bg="/images/demo/bg-comps.jpg" crumb={t.crumb} title={t.title} desc={t.desc} />

            <section className="bg-bg px-4 py-12">
                <div className="mx-auto max-w-7xl">
                    <ActiveFilters filters={filters} path="/compounds" />
                </div>

                <div className="mx-auto grid max-w-7xl gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {compounds.map((c, i) => (
                        <Reveal key={c.id} delay={i * 90}>
                            <article className="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-bg transition duration-200 hover:-translate-y-1 hover:border-primary/50 hover:shadow-[0_12px_30px_rgba(11,18,32,0.07)]">
                                <div className="relative h-48 overflow-hidden bg-surface">
                                    <img
                                        src={c.image}
                                        alt={c.name}
                                        loading="lazy"
                                        className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    />
                                    {c.new && (
                                        <span className="absolute start-3 top-3 rounded-full bg-primary px-3 py-2 text-[11px] font-extrabold text-primary-fg">
                                            {t.newTag}
                                        </span>
                                    )}
                                </div>

                                <div className="flex flex-1 flex-col gap-2 p-4">
                                    <h3 className="text-lg font-extrabold leading-relaxed text-secondary">{c.name}</h3>

                                    <div className="flex items-center gap-2 text-xs font-bold text-muted">
                                        <MapPin size={13} className="shrink-0 text-primary" />
                                        <span>
                                            {c.area} · {c.developer}
                                        </span>
                                    </div>

                                    <p className="text-sm leading-[1.8] text-muted">{c.desc}</p>

                                    <div className="mt-2 grid grid-cols-2 gap-y-4 rounded-xl bg-surface py-4">
                                        {cell(t.from, c.starting, true)}
                                        <div className="border-s border-gray-200">{cell(t.down, c.down)}</div>
                                        {cell(t.plan, c.years)}
                                        <div className="border-s border-gray-200">{cell(t.delivery, c.delivery)}</div>
                                    </div>

                                    <div className="mt-auto flex items-center gap-2 pt-1 text-[11px] font-bold text-muted">
                                        <CalendarCheck size={13} className="text-primary" />
                                        {ar ? "تاريخ التسليم موثّق في العقد" : "Delivery date documented in the contract"}
                                    </div>

                                    {wa && (
                                        <a
                                            href={`https://wa.me/${wa}?text=${encodeURIComponent((ar ? "مهتم بمشروع: " : "Interested in project: ") + c.name)}`}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="mt-2 block rounded-brand border-2 border-primary py-3 text-center text-[13px] font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg"
                                        >
                                            {t.wa}
                                        </a>
                                    )}
                                </div>
                            </article>
                        </Reveal>
                    ))}
                </div>
            </section>
        </SiteLayout>
    );
}
