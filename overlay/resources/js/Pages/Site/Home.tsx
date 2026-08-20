import { Link, usePage } from "@inertiajs/react";
import CompoundCard from "@/Components/site/CompoundCard";
import CountUp from "@/Components/site/CountUp";
import FrameMedia from "@/Components/site/FrameMedia";
import HeroSearch from "@/Components/site/HeroSearch";
import PropertyCard from "@/Components/site/PropertyCard";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Area, Compound, Property, SearchOptions, SharedProps } from "@/lib/types";

/**
 * Home v1.0 — منفّذة من كانفاس Claude Design "منصة إكس هومز العقارية".
 * الأقسام: هيرو + شريط بحث + عقارات مختارة + كمبوندات + خطوات العمل + المناطق + بانر المقارنة.
 * كل الألوان من توكنز الثيم (قاعدة البيانات) — مفيش قيم لونية ثابتة هنا.
 */

const copy = {
    ar: {
        badge: "إطلاق جديد · 6 كمبوندات في العاصمة الإدارية",
        h1a: "وحدتك الجديدة",
        h1b: "باختيار مدروس مش صدفة",
        sub: "منصة عقارية مصرية بتجمع وحدات معتمدة في القاهرة الجديدة والعاصمة الإدارية والإسكندرية، مع بيانات كاملة عن السعر وأنظمة السداد وموعد التسليم قبل أي معاينة.",
        cta: "اتفرج على العقارات",
        cta2: "الكمبوندات",
        stats: [
            ["1240", "وحدة متاحة"],
            ["38", "كمبوند مسجّل"],
            ["12", "سنة في السوق"],
        ],
        avgLabel: "متوسط سعر المتر · التجمع",
        avgValue: "EGP 34,500",
        planLabel: "أنظمة تقسيط لحد",
        planValue: "10 سنوات",
        propsTitle: "عقارات مختارة",
        propsSub: "وحدات تمت مراجعة أوراقها ومعاينتها من فريقنا خلال آخر أسبوعين.",
        propsAll: "كل العقارات",
        compsTitle: "كمبوندات بأنظمة سداد واضحة",
        compsSub: "المقدم والتقسيط وسعر البداية معروضين قبل ما تكلّم حد.",
        compsAll: "كل الكمبوندات",
        stepsTitle: "من الاستفسار لتسليم المفتاح",
        stepsSub: "أربع خطوات ثابتة، ومستشار واحد مسؤول عن ملفك من أول مكالمة لحد العقد المسجّل.",
        steps: [
            ["تحديد الميزانية والاحتياج", "مكالمة 15 دقيقة نطلع منها بقائمة مختصرة مناسبة لك فعلًا."],
            ["معاينة مرتبة في يوم واحد", "جدول زيارات لثلاث وحدات كحد أقصى، بمواصلات من عندنا."],
            ["مراجعة الأوراق والتفاوض", "تحقق من التسجيل والرخصة، وتفاوض على السعر ونظام السداد."],
            ["التعاقد والتسليم", "حضور محامي المنصة، ومتابعة أقساط ما بعد التعاقد."],
        ],
        areasTitle: "مناطق بنغطيها بالتفصيل",
        ctaTitle: "محتار بين وحدتين؟",
        ctaSub: "ابعتلنا الاختيارات وهنرجّعلك مقارنة مكتوبة بالسعر وسعر المتر ونظام السداد وتاريخ التسليم.",
        ctaBtn: "تواصل معنا",
        ctaWa: "كلمنا واتساب",
    },
    en: {
        badge: "New launch · 6 compounds in the New Capital",
        h1a: "Your next home",
        h1b: "chosen by study, not by chance",
        sub: "An Egyptian real-estate platform gathering verified units in New Cairo, the New Capital and Alexandria — with full data on price, payment plans and delivery date before any viewing.",
        cta: "Browse properties",
        cta2: "Compounds",
        stats: [
            ["1240", "Available units"],
            ["38", "Registered compounds"],
            ["12", "Years in market"],
        ],
        avgLabel: "Avg. price / m² · Settlement",
        avgValue: "EGP 34,500",
        planLabel: "Installments up to",
        planValue: "10 years",
        propsTitle: "Selected properties",
        propsSub: "Units whose papers were reviewed and inspected by our team in the last two weeks.",
        propsAll: "All properties",
        compsTitle: "Compounds with clear payment plans",
        compsSub: "Down payment, installments and starting price shown before you talk to anyone.",
        compsAll: "All compounds",
        stepsTitle: "From enquiry to handover",
        stepsSub: "Four fixed steps, and one advisor responsible for your file from the first call to the registered contract.",
        steps: [
            ["Defining budget and needs", "A 15-minute call that produces a shortlist that actually fits you."],
            ["An organised viewing in one day", "A visit schedule for three units max, with transport on us."],
            ["Paperwork review and negotiation", "Registration and licence checks, plus price and plan negotiation."],
            ["Contract and handover", "Our lawyer attends, and we follow up post-contract instalments."],
        ],
        areasTitle: "Areas we cover in depth",
        ctaTitle: "Torn between two units?",
        ctaSub: "Send us your options and we will return a written comparison of price, price per m², payment plan and delivery date.",
        ctaBtn: "Contact us",
        ctaWa: "WhatsApp us",
    },
};

export default function Home({
    latestProperties,
    latestCompounds,
    areas,
    searchOptions,
}: {
    latestProperties: Property[];
    latestCompounds: Compound[];
    areas: Area[];
    searchOptions: SearchOptions;
}) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const t = copy[locale] ?? copy.ar;
    const wa = settings.contact?.whatsapp;
    const heroMedia = settings.branding?.hero_media;
    const processMedia = settings.branding?.process_media;

    const sectionTitle = (title: string, sub: string, href: string, allLabel: string) => (
        <div className="mb-6 flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-end">
            <Reveal>
                <h2 className="text-3xl font-extrabold text-secondary">{title}</h2>
                <p className="mt-2 max-w-xl text-base leading-relaxed text-muted">{sub}</p>
            </Reveal>
            <Link
                href={href}
                className="shrink-0 rounded-brand border-2 border-secondary px-6 py-3 text-sm font-extrabold text-secondary transition hover:bg-secondary hover:text-white"
            >
                {allLabel}
            </Link>
        </div>
    );

    return (
        <SiteLayout>
            {/* ---------------- الهيرو الرئيسي: فيديو خلفية + بحث ---------------- */}
            <HeroSearch options={searchOptions} variant={settings.theme?.hero_variant ?? "video"} />

            {/* ---------------- هيرو ثانوي ---------------- */}
            <section className="bg-surface px-4 pb-20 pt-10">
                <div className="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[1.05fr_0.95fr]">
                    <Reveal>
                        <span className="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-4 py-2 text-[11px] font-extrabold text-secondary">
                            {t.badge}
                        </span>

                        <h1 className="mt-4 text-4xl font-black leading-[1.25] text-secondary md:text-6xl">
                            {t.h1a}
                            <br />
                            {t.h1b}
                        </h1>

                        <p className="mt-4 max-w-lg text-base leading-[1.9] text-muted">{t.sub}</p>

                        <div className="mt-6 flex flex-wrap items-center gap-3.5">
                            <Link
                                href={`/${locale}/properties`}
                                className="rounded-brand bg-primary px-8 py-3.5 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover"
                            >
                                {t.cta}
                            </Link>
                            <Link
                                href={`/${locale}/compounds`}
                                className="rounded-brand border-2 border-secondary px-8 py-3 text-sm font-extrabold text-secondary transition hover:bg-secondary hover:text-white"
                            >
                                {t.cta2}
                            </Link>
                        </div>

                        <div className="mt-8 flex flex-wrap items-center gap-6">
                            {t.stats.map(([value, label], i) => (
                                <div key={label} className="flex items-center gap-6">
                                    {i > 0 && <span className="h-10 w-px bg-gray-200" aria-hidden />}
                                    <span className="flex flex-col gap-1">
                                        <span className="text-[26px] font-black text-primary" dir="ltr">
                                            <CountUp value={value} />
                                        </span>
                                        <span className="text-xs font-bold text-muted">{label}</span>
                                    </span>
                                </div>
                            ))}
                        </div>
                    </Reveal>

                    <Reveal delay={140}>
                        <div className="relative">
                            <FrameMedia src={heroMedia} poster="/images/demo/hero.jpg" alt="" ratio="4 / 4.6" priority />
                            <div className="absolute inset-x-4 bottom-4 grid grid-cols-[1fr_1px_1fr] items-center gap-4 rounded-2xl bg-bg/90 p-4 backdrop-blur">
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-bold text-muted">{t.avgLabel}</span>
                                    <span className="text-lg font-extrabold text-primary" dir="ltr">
                                        {t.avgValue}
                                    </span>
                                </div>
                                <span className="h-full w-px bg-gray-200" aria-hidden />
                                <div className="flex flex-col gap-1">
                                    <span className="text-xs font-bold text-muted">{t.planLabel}</span>
                                    <span className="text-lg font-extrabold text-primary">{t.planValue}</span>
                                </div>
                            </div>
                        </div>
                    </Reveal>
                </div>
            </section>


            {/* ---------------- عقارات مختارة ---------------- */}
            <section className="bg-bg px-4 py-14">
                <div className="mx-auto max-w-7xl">
                    {sectionTitle(t.propsTitle, t.propsSub, `/${locale}/properties`, t.propsAll)}
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {latestProperties.map((p, i) => (
                            <Reveal key={p.id} delay={i * 110}>
                                <PropertyCard p={p} ar={ar} wa={wa} />
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>

            {/* ---------------- كمبوندات ---------------- */}
            <section className="bg-surface px-4 py-14">
                <div className="mx-auto max-w-7xl">
                    {sectionTitle(t.compsTitle, t.compsSub, `/${locale}/compounds`, t.compsAll)}
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {latestCompounds.map((c, i) => (
                            <Reveal key={c.id} delay={i * 110}>
                                <CompoundCard c={c} ar={ar} wa={wa} />
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>

            {/* ---------------- من الاستفسار لتسليم المفتاح ---------------- */}
            <section className="bg-bg px-4 py-14">
                <div className="mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[1fr_1.05fr]">
                    <Reveal>
                        <FrameMedia src={processMedia} poster="/images/demo/process.jpg" alt="" ratio="5 / 4" />
                    </Reveal>

                    <Reveal delay={140}>
                        <h2 className="text-3xl font-extrabold text-secondary">{t.stepsTitle}</h2>
                        <p className="mt-2 max-w-lg text-base leading-[1.9] text-muted">{t.stepsSub}</p>

                        <div className="mt-6 flex flex-col gap-4">
                            {t.steps.map(([title, desc], i) => (
                                <div key={title} className="flex items-start gap-4">
                                    <span
                                        dir="ltr"
                                        className="flex h-[38px] w-[38px] shrink-0 items-center justify-center rounded-full border border-primary/40 bg-primary/10 text-sm font-extrabold text-secondary"
                                    >
                                        0{i + 1}
                                    </span>
                                    <div>
                                        <h3 className="text-[17px] font-extrabold text-secondary">{title}</h3>
                                        <p className="mt-2 text-sm leading-[1.8] text-muted">{desc}</p>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </Reveal>
                </div>
            </section>

            {/* ---------------- المناطق ---------------- */}
            <section className="bg-surface px-4 py-14">
                <div className="mx-auto max-w-7xl">
                    <Reveal>
                        <h2 className="mb-6 text-3xl font-extrabold text-secondary">{t.areasTitle}</h2>
                    </Reveal>
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {areas.map((a, i) => (
                            <Reveal key={a.id} delay={i * 110}>
                                <article className="group relative h-64 overflow-hidden rounded-3xl border border-gray-100 bg-bg transition duration-200 hover:-translate-y-1 hover:border-primary/50">
                                    <img
                                        src={a.image}
                                        alt={a.name}
                                        loading="lazy"
                                        className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                    />
                                    <div className="pointer-events-none absolute inset-x-4 bottom-3.5 flex items-center justify-between gap-3 rounded-2xl bg-bg/90 p-4 backdrop-blur">
                                        <span className="flex flex-col gap-1">
                                            <span className="text-[17px] font-extrabold text-secondary">{a.name}</span>
                                            <span className="text-xs font-bold text-muted">{a.note}</span>
                                        </span>
                                        <span className="shrink-0 text-lg font-extrabold text-primary">{a.count}</span>
                                    </div>
                                </article>
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>

            {/* ---------------- بانر المقارنة ---------------- */}
            <section className="bg-bg px-4 pb-20">
                <Reveal>
                    <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-8 rounded-3xl border border-primary/30 bg-primary/10 p-8">
                        <div>
                            <h2 className="text-3xl font-extrabold text-secondary">{t.ctaTitle}</h2>
                            <p className="mt-2 max-w-xl text-base leading-[1.8] text-muted">{t.ctaSub}</p>
                        </div>
                        <div className="flex flex-wrap gap-3.5">
                            <Link
                                href={`/${locale}/contact`}
                                className="rounded-brand bg-primary px-8 py-3.5 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover"
                            >
                                {t.ctaBtn}
                            </Link>
                            {wa && (
                                <a
                                    href={`https://wa.me/${wa}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="rounded-brand border-2 border-secondary px-8 py-3 text-sm font-extrabold text-secondary transition hover:bg-secondary hover:text-white"
                                >
                                    {t.ctaWa}
                                </a>
                            )}
                        </div>
                    </div>
                </Reveal>
            </section>
        </SiteLayout>
    );
}
