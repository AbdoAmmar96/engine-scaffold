import { Link, usePage } from "@inertiajs/react";
import { BadgeCheck, Building2, Headset, MessageCircle, RefreshCw, Search, UserCheck } from "lucide-react";
import { lazy, Suspense } from "react";
import BrandVideo from "@/Components/site/BrandVideo";
import CompoundCard from "@/Components/site/CompoundCard";
import CountUp from "@/Components/site/CountUp";
import PropertyCard from "@/Components/site/PropertyCard";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Compound, Property, SharedProps } from "@/lib/types";

const HeroWebGL = lazy(() => import("@/Components/site/HeroWebGL"));

/**
 * Home v0.3 — هيرو WebGL + أنيميشن + أقسام: أحدث العقارات، الفيديو التعريفي،
 * أحدث الكمبوندات، كيف نعمل، ليه تختارنا، بانر CTA.
 * في المرحلة 3 الأقسام دي بتتحول لبلوكات feed بيتحكم فيها الـ Block Builder.
 */

const copy = {
    ar: {
        badge: "منصة عقارية — نسخة التأسيس",
        headline: "حوِّل حلمك لعنوان",
        sub: "دوّر بين مئات العقارات والكمبوندات بأسعار حقيقية بتتحدث يوميًا — ومستشارك معاك لحد ما تستلم المفتاح.",
        cta: "تصفح العقارات",
        cta2: "اكتشف الكمبوندات",
        stats: [
            ["6", "عقارات تجريبية"],
            ["4", "كمبوندات تجريبية"],
            ["2", "لغة عربي/إنجليزي"],
            ["5", "أنماط هيرو"],
        ],
        latestProps: "أحدث العقارات",
        latestComps: "أحدث الكمبوندات",
        viewAll: "عرض الكل",
        videoTitle: "شاهد قصتنا في 30 ثانية",
        videoSub: "فيديو تعريفي عن المنصة — إنتاج بالذكاء الاصطناعي.",
        howTitle: "كيف نعمل؟",
        how: [
            ["دوّر وقارن", "تصفح العقارات والكمبوندات وقارن الأسعار والتقسيط.", Search],
            ["كلّمنا", "ابعت استفسارك واتساب أو من فورم التواصل في ثواني.", MessageCircle],
            ["مستشار يقفلها معاك", "خبير بيرافقك في المعاينة والتفاوض لحد التعاقد.", UserCheck],
        ],
        whyTitle: "ليه تختارنا؟",
        why: [
            ["إعلانات مختارة", "كل إعلان بيتراجع ويتعتمد قبل النشر.", BadgeCheck],
            ["مطوّرون موثوقون", "بنتعامل مع أفضل المشاريع في السوق.", Building2],
            ["مستشار مخصص", "خبير معاك من أول سؤال لحد التعاقد.", Headset],
            ["تحديث يومي", "أسعار ووحدات جديدة كل يوم.", RefreshCw],
        ],
        bannerTitle: "عندك وحدة عايز تبيعها أو تأجرها؟",
        bannerSub: "اعرضها مجانًا وهيوصلك مشترين جادين.",
        bannerCta: "كلّمنا دلوقتي",
    },
    en: {
        badge: "Real-estate platform — foundation build",
        headline: "Turn your dream into an address",
        sub: "Browse hundreds of properties and compounds with real, daily-updated prices — with an advisor by your side until you get the keys.",
        cta: "Browse properties",
        cta2: "Explore compounds",
        stats: [
            ["6", "Demo properties"],
            ["4", "Demo compounds"],
            ["2", "Languages AR/EN"],
            ["5", "Hero variants"],
        ],
        latestProps: "Latest properties",
        latestComps: "Latest compounds",
        viewAll: "View all",
        videoTitle: "Watch our story in 30 seconds",
        videoSub: "An AI-produced brand video about the platform.",
        howTitle: "How it works",
        how: [
            ["Search and compare", "Browse properties and compounds, compare prices and installments.", Search],
            ["Talk to us", "Send your inquiry on WhatsApp or the contact form in seconds.", MessageCircle],
            ["An advisor closes it with you", "An expert joins you through viewing and negotiation to contract.", UserCheck],
        ],
        whyTitle: "Why choose us?",
        why: [
            ["Verified listings", "Every listing is reviewed before it goes live.", BadgeCheck],
            ["Trusted developers", "We work with the market's best projects.", Building2],
            ["Dedicated advisor", "An expert with you from question to contract.", Headset],
            ["Daily updates", "New units and prices every day.", RefreshCw],
        ],
        bannerTitle: "Have a unit to sell or rent?",
        bannerSub: "List it free and reach serious buyers.",
        bannerCta: "Talk to us now",
    },
};

export default function Home({
    latestProperties,
    latestCompounds,
}: {
    latestProperties: Property[];
    latestCompounds: Compound[];
}) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const t = copy[locale] ?? copy.ar;
    const wa = settings.contact?.whatsapp;
    const logo = settings.branding?.logo_path;
    const webgl = (settings.theme?.hero_variant ?? "webgl") === "webgl";

    return (
        <SiteLayout>
            {/* ---------------- Hero: WebGL خلفية حية بألوان الهوية ---------------- */}
            <section className="relative overflow-hidden border-b border-gray-100 bg-surface">
                {webgl && (
                    <Suspense fallback={null}>
                        <HeroWebGL />
                    </Suspense>
                )}

                <div className="relative z-10 mx-auto flex max-w-7xl flex-col items-center px-4 py-20 text-center md:py-24">
                    {logo && (
                        <Reveal>
                            <img src={logo} alt="" className="mx-auto h-20 w-auto md:h-24" />
                        </Reveal>
                    )}

                    <Reveal delay={100}>
                        <span className="mt-5 inline-block rounded-full border border-primary/40 bg-primary/10 px-4 py-1.5 text-xs font-extrabold text-secondary">
                            {t.badge}
                        </span>
                    </Reveal>

                    <Reveal delay={180}>
                        <h1 className="mt-6 max-w-3xl text-4xl leading-tight text-secondary md:text-6xl">{t.headline}</h1>
                    </Reveal>

                    <Reveal delay={260}>
                        <p className="mt-5 max-w-xl text-base leading-relaxed text-muted">{t.sub}</p>
                    </Reveal>

                    <Reveal delay={340}>
                        <div className="mt-8 flex flex-wrap justify-center gap-3">
                            <Link
                                href={`/${locale}/properties`}
                                className="rounded-brand bg-primary px-8 py-3.5 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover"
                            >
                                {t.cta}
                            </Link>
                            <Link
                                href={`/${locale}/compounds`}
                                className="rounded-brand border-2 border-secondary bg-bg/60 px-8 py-3 text-sm font-extrabold text-secondary transition hover:bg-secondary hover:text-white"
                            >
                                {t.cta2}
                            </Link>
                        </div>
                    </Reveal>

                    <div className="mt-16 grid w-full max-w-3xl grid-cols-2 gap-4 md:grid-cols-4">
                        {t.stats.map(([value, label], i) => (
                            <Reveal key={label} delay={420 + i * 90}>
                                <div className="rounded-2xl border border-gray-100 bg-bg/80 p-5 shadow-sm backdrop-blur">
                                    <div className="text-3xl font-extrabold text-primary">
                                        <CountUp value={value} />
                                    </div>
                                    <div className="mt-1 text-xs font-bold text-muted">{label}</div>
                                </div>
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>

            {/* ---------------- أحدث العقارات ---------------- */}
            <section className="bg-bg">
                <div className="mx-auto max-w-7xl px-4 py-16">
                    <Reveal>
                        <div className="flex items-center justify-between">
                            <h2 className="text-3xl text-secondary">{t.latestProps}</h2>
                            <Link href={`/${locale}/properties`} className="text-sm font-extrabold text-primary hover:text-primary-hover">
                                {t.viewAll} ←
                            </Link>
                        </div>
                    </Reveal>
                    <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {latestProperties.map((p, i) => (
                            <Reveal key={p.id} delay={i * 110}>
                                <PropertyCard p={p} ar={ar} wa={wa} />
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>

            {/* ---------------- الفيديو التعريفي ---------------- */}
            <section className="bg-surface">
                <div className="mx-auto max-w-5xl px-4 py-16">
                    <Reveal>
                        <div className="text-center">
                            <h2 className="text-3xl text-secondary">{t.videoTitle}</h2>
                            <p className="mt-2 text-sm text-muted">{t.videoSub}</p>
                        </div>
                    </Reveal>
                    <Reveal delay={150}>
                        <div className="mt-8">
                            <BrandVideo />
                        </div>
                    </Reveal>
                </div>
            </section>

            {/* ---------------- أحدث الكمبوندات ---------------- */}
            <section className="bg-bg">
                <div className="mx-auto max-w-7xl px-4 py-16">
                    <Reveal>
                        <div className="flex items-center justify-between">
                            <h2 className="text-3xl text-secondary">{t.latestComps}</h2>
                            <Link href={`/${locale}/compounds`} className="text-sm font-extrabold text-primary hover:text-primary-hover">
                                {t.viewAll} ←
                            </Link>
                        </div>
                    </Reveal>
                    <div className="mt-8 grid gap-6 sm:grid-cols-2">
                        {latestCompounds.map((c, i) => (
                            <Reveal key={c.id} delay={i * 110}>
                                <CompoundCard c={c} ar={ar} wa={wa} />
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>

            {/* ---------------- كيف نعمل ---------------- */}
            <section className="bg-surface">
                <div className="mx-auto max-w-7xl px-4 py-16">
                    <Reveal>
                        <h2 className="text-center text-3xl text-secondary">{t.howTitle}</h2>
                    </Reveal>
                    <div className="mt-10 grid gap-5 md:grid-cols-3">
                        {t.how.map(([title, desc, Icon], i) => (
                            <Reveal key={title as string} delay={i * 130}>
                                <div className="relative rounded-2xl border border-gray-100 bg-bg p-6">
                                    <span className="absolute -top-3 end-5 rounded-full bg-secondary px-3 py-1 text-[11px] font-extrabold text-white">
                                        0{i + 1}
                                    </span>
                                    <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                        {/* @ts-expect-error lucide component from tuple */}
                                        <Icon size={22} />
                                    </span>
                                    <h3 className="mt-4 text-base font-extrabold text-secondary">{title as string}</h3>
                                    <p className="mt-2 text-sm leading-relaxed text-muted">{desc as string}</p>
                                </div>
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>

            {/* ---------------- ليه تختارنا ---------------- */}
            <section className="bg-bg">
                <div className="mx-auto max-w-7xl px-4 py-16">
                    <Reveal>
                        <h2 className="text-center text-3xl text-secondary">{t.whyTitle}</h2>
                    </Reveal>
                    <div className="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        {t.why.map(([title, desc, Icon], i) => (
                            <Reveal key={title as string} delay={i * 100}>
                                <div className="rounded-2xl border border-gray-100 bg-bg p-6 transition hover:border-primary/50 hover:shadow-sm">
                                    <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                        {/* @ts-expect-error lucide component from tuple */}
                                        <Icon size={22} />
                                    </span>
                                    <h3 className="mt-4 text-base font-extrabold text-secondary">{title as string}</h3>
                                    <p className="mt-2 text-sm leading-relaxed text-muted">{desc as string}</p>
                                </div>
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>

            {/* ---------------- CTA banner ---------------- */}
            <section className="bg-bg pb-20">
                <div className="mx-auto max-w-7xl px-4">
                    <Reveal>
                        <div className="flex flex-col items-center justify-between gap-6 rounded-3xl border border-primary/30 bg-primary/10 px-8 py-10 text-center md:flex-row md:text-start">
                            <div>
                                <h2 className="text-2xl text-secondary">{t.bannerTitle}</h2>
                                <p className="mt-2 text-sm text-muted">{t.bannerSub}</p>
                            </div>
                            <Link
                                href={`/${locale}/contact`}
                                className="shrink-0 rounded-brand bg-primary px-8 py-3.5 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover"
                            >
                                {t.bannerCta}
                            </Link>
                        </div>
                    </Reveal>
                </div>
            </section>
        </SiteLayout>
    );
}
