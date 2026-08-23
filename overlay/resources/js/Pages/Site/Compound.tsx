import { Link, usePage } from "@inertiajs/react";
import {
    ArrowLeft,
    Building2,
    CalendarCheck,
    Check,
    MapPin,
    MessageCircle,
    Phone,
    Wallet,
} from "lucide-react";
import Gallery from "@/Components/site/Gallery";
import LeadForm from "@/Components/site/LeadForm";
import PageHero from "@/Components/site/PageHero";
import PropertyCard from "@/Components/site/PropertyCard";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { CompoundDetail, Property, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "الكمبوندات",
        back: "كل الكمبوندات",
        plan: "نظام السداد",
        about: "عن المشروع",
        features: "مميزات المشروع",
        units: "الوحدات المتاحة في المشروع",
        noUnits: "لا توجد وحدات معروضة في هذا المشروع حاليًا — تواصل معنا ونبحث لك عن المتاح.",
        from: "يبدأ من",
        down: "المقدم",
        years: "التقسيط",
        delivery: "التسليم",
        developer: "المطوّر",
        area: "المنطقة",
        newTag: "إطلاق جديد",
        wa: "استفسر واتساب",
        call: "اتصل بنا",
        form: "اطلب عرض الأسعار",
        formTitle: "اطلب عرض أسعار المشروع",
        formNote: "يصل الطلب مباشرة إلى الشركة المسؤولة عن المشروع.",
        note: "أنظمة السداد وتواريخ التسليم كما وردت من المطوّر، ويجري تأكيدها في العقد.",
    },
    en: {
        crumb: "Compounds",
        back: "All compounds",
        plan: "Payment plan",
        about: "About the project",
        features: "Project features",
        units: "Units available in this project",
        noUnits: "No units listed for this project right now — talk to us and we'll check what's available.",
        from: "From",
        down: "Down payment",
        years: "Installments",
        delivery: "Delivery",
        developer: "Developer",
        area: "Area",
        newTag: "New launch",
        wa: "Ask on WhatsApp",
        call: "Call us",
        form: "Request a quote",
        formTitle: "Request a project quote",
        formNote: "Your request goes straight to the company behind this project.",
        note: "Payment plans and delivery dates are as stated by the developer, and confirmed in the contract.",
    },
};

export default function CompoundPage({ compound, units }: { compound: CompoundDetail; units: Property[] }) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const t = copy[locale] ?? copy.ar;

    const wa = settings.contact?.whatsapp;
    const phone = settings.contact?.phone;

    const cell = (label: string, value: string, gold = false) =>
        value ? (
            <div className="flex flex-col items-center gap-1.5 px-3 py-4">
                <span className="text-[11px] font-bold text-muted">{label}</span>
                <span
                    className={`text-center text-sm font-extrabold ${gold ? "text-primary" : "text-secondary"}`}
                    dir="auto"
                >
                    {value}
                </span>
            </div>
        ) : null;

    return (
        <SiteLayout>
            <PageHero
                bg={compound.image}
                crumb={t.crumb}
                crumbHref={`/${locale}/compounds`}
                title={compound.name}
                desc={[compound.area, compound.developer].filter(Boolean).join(" · ")}
            >
                <div className="flex flex-wrap items-center gap-2.5 text-[12px] font-extrabold">
                    {compound.new && (
                        <span className="rounded-full bg-primary px-3 py-1.5 text-primary-fg">{t.newTag}</span>
                    )}
                    {compound.area && (
                        <span className="flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-white/85">
                            <MapPin size={12} className="text-primary" />
                            {compound.area}
                        </span>
                    )}
                    {compound.developer && (
                        <span className="flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-white/85">
                            <Building2 size={12} className="text-primary" />
                            {compound.developer}
                        </span>
                    )}
                </div>
            </PageHero>

            <section className="bg-bg px-4 py-12">
                <div className="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[minmax(0,1fr)_21rem]">
                    <div className="flex min-w-0 flex-col gap-8">
                        <Reveal>
                            <Gallery items={compound.gallery} alt={compound.name} />
                        </Reveal>

                        <Reveal>
                            <div>
                                <h2 className="mb-4 flex items-center gap-2 text-xl font-extrabold text-secondary">
                                    <Wallet size={19} className="text-primary" />
                                    {t.plan}
                                </h2>
                                <div className="grid grid-cols-2 divide-x divide-y divide-gray-100 rounded-2xl bg-surface sm:grid-cols-4 sm:divide-y-0 rtl:divide-x-reverse">
                                    {cell(t.from, compound.starting, true)}
                                    {cell(t.down, compound.down)}
                                    {cell(t.years, compound.years)}
                                    {cell(t.delivery, compound.delivery)}
                                </div>
                                <p className="mt-3 flex items-center gap-2 text-[11px] font-bold text-muted">
                                    <CalendarCheck size={13} className="text-primary" />
                                    {t.note}
                                </p>
                            </div>
                        </Reveal>

                        {compound.desc && (
                            <Reveal>
                                <div>
                                    <h2 className="mb-3 text-xl font-extrabold text-secondary">{t.about}</h2>
                                    <div className="flex flex-col gap-4">
                                        {compound.desc
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

                        {compound.features.length > 0 && (
                            <Reveal>
                                <div>
                                    <h2 className="mb-4 text-xl font-extrabold text-secondary">{t.features}</h2>
                                    <ul className="grid gap-2.5 sm:grid-cols-2">
                                        {compound.features.map((f, i) => (
                                            <li key={i} className="flex items-start gap-2.5 text-[14px] text-text">
                                                <span className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                                    <Check size={12} />
                                                </span>
                                                {f}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </Reveal>
                        )}

                        <div id="lead" className="scroll-mt-24">
                            <h2 className="mb-1 text-xl font-extrabold text-secondary">{t.formTitle}</h2>
                            <p className="mb-4 text-[13px] text-muted">{t.formNote}</p>
                            <LeadForm compoundId={compound.id} source="compound" subject={compound.name} />
                        </div>

                        <Link
                            href={`/${locale}/compounds`}
                            className="flex w-fit items-center gap-2 text-[13px] font-extrabold text-secondary transition hover:text-primary"
                        >
                            <ArrowLeft size={15} className="text-primary ltr:rotate-180" />
                            {t.back}
                        </Link>
                    </div>

                    <aside className="flex flex-col gap-5 lg:sticky lg:top-24 lg:self-start">
                        <div className="rounded-2xl border border-gray-100 bg-bg p-6 shadow-[0_4px_18px_rgba(11,18,32,0.05)]">
                            <span className="text-[11px] font-bold text-muted">{t.from}</span>
                            <div className="mt-1 text-2xl font-black text-primary" dir="ltr">
                                {compound.starting}
                            </div>

                            <div className="mt-5 flex flex-col gap-2.5">
                                {wa && (
                                    <a
                                        href={`https://wa.me/${wa}?text=${encodeURIComponent(
                                            (ar ? "مهتم بمشروع: " : "Interested in project: ") + compound.name,
                                        )}`}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="flex items-center justify-center gap-2 rounded-brand bg-primary py-3 text-[13px] font-extrabold text-primary-fg transition hover:opacity-90"
                                    >
                                        <MessageCircle size={16} />
                                        {t.wa}
                                    </a>
                                )}

                                {phone && (
                                    <a
                                        href={`tel:${phone}`}
                                        className="flex items-center justify-center gap-2 rounded-brand border-2 border-primary py-3 text-[13px] font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg"
                                    >
                                        <Phone size={16} />
                                        {t.call}
                                    </a>
                                )}

                                <a
                                    href="#lead"
                                    className="flex items-center justify-center gap-2 rounded-brand border border-gray-200 py-3 text-[13px] font-extrabold text-secondary transition hover:border-primary hover:text-primary"
                                >
                                    {t.form}
                                </a>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            <section className="bg-surface px-4 py-14">
                <div className="mx-auto max-w-7xl">
                    <h2 className="mb-6 text-2xl font-extrabold text-secondary">{t.units}</h2>

                    {units.length > 0 ? (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {units.map((p, i) => (
                                <Reveal key={p.id} delay={i * 80}>
                                    <PropertyCard p={p} ar={ar} wa={wa} />
                                </Reveal>
                            ))}
                        </div>
                    ) : (
                        <p className="rounded-2xl border border-gray-100 bg-bg px-6 py-10 text-center text-sm text-muted">
                            {t.noUnits}
                        </p>
                    )}
                </div>
            </section>
        </SiteLayout>
    );
}
