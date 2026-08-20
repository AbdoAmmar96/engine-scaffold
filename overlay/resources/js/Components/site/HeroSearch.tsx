import { Link, usePage } from "@inertiajs/react";
import { Headset, Search } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import CountUp from "@/Components/site/CountUp";
import { lazy, Suspense } from "react";
import type { SearchOptions, SharedProps } from "@/lib/types";

/**
 * الهيرو الرئيسي — فيديو خلفية بعرض الشاشة (بستايل Booking) والمحتوى فوقه:
 * عنوان + وصف مختصر + كارت بحث بتبويبين وفلاتر + أرقام المنصة.
 *
 * الفيديو مؤجّل زي FrameMedia: الـ poster بيظهر فورًا والفيديو بيتحمّل
 * بعد ما المتصفح يفضى — عشان الصفحة متستناهوش.
 */

const copy = {
    ar: {
        h1a: "حوِّل أحلامك إلى",
        h1b: "عناوين",
        sub: "اكتشف أفضل عقارات مصر — وحدات معتمدة ببيانات كاملة عن السعر وأنظمة السداد وموعد التسليم قبل أي معاينة.",
        tabProperty: "ابحث عن عقار",
        tabProject: "بحث بالمشروع والمطوّر",
        sale: "بيع",
        rent: "إيجار",
        qLabel: "ابحث",
        qPlaceholder: "ابحث بالمنطقة أو الكمبوند أو المطوّر…",
        qPlaceholderProject: "اكتب اسم المشروع أو المطوّر…",
        typeLabel: "النوع",
        typeAll: "كل الأنواع",
        locLabel: "المنطقة",
        locAll: "كل المناطق",
        search: "ابحث",
        helpTitle: "تحتاج مساعدة؟",
        helpCta: "تحدث مع خبير",
    },
    en: {
        h1a: "Turn your dreams into",
        h1b: "addresses",
        sub: "Discover Egypt's best properties — verified units with full data on price, payment plans and delivery date before any viewing.",
        tabProperty: "Find a property",
        tabProject: "Search by project or developer",
        sale: "Sale",
        rent: "Rent",
        qLabel: "Search",
        qPlaceholder: "Search by area, compound or developer…",
        qPlaceholderProject: "Type a project or developer name…",
        typeLabel: "Type",
        typeAll: "All types",
        locLabel: "Area",
        locAll: "All areas",
        search: "Search",
        helpTitle: "Need help?",
        helpCta: "Talk to an expert",
    },
};

const HeroWebGL = lazy(() => import("@/Components/site/HeroWebGL"));

export default function HeroSearch({ options, variant = "video" }: { options: SearchOptions; variant?: string }) {
    const { locale, settings } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;
    const wa = settings.contact?.whatsapp;

    const video = settings.branding?.hero_bg_video;
    const poster = "/images/demo/hero-bg.jpg";

    const [tab, setTab] = useState<"property" | "project">("property");
    const [purpose, setPurpose] = useState<"sale" | "rent">("sale");
    const [playVideo, setPlayVideo] = useState(false);
    const box = useRef<HTMLElement>(null);

    // نأجّل الفيديو لحد ما المتصفح يفضى، ومنشغّلهوش مع reduced-motion
    useEffect(() => {
        if (!video) return;
        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

        const start = () => setPlayVideo(true);
        const hasIdle = typeof window.requestIdleCallback === "function";
        const id = hasIdle ? window.requestIdleCallback(start, { timeout: 3000 }) : window.setTimeout(start, 600);

        return () => {
            if (hasIdle) window.cancelIdleCallback(id as number);
            else clearTimeout(id as number);
        };
    }, [video]);

    const tabBtn = (key: "property" | "project", label: string) => (
        <button
            type="button"
            onClick={() => setTab(key)}
            className={`rounded-t-xl px-5 py-3 text-sm font-extrabold transition ${
                tab === key ? "bg-bg text-secondary" : "bg-bg/15 text-white hover:bg-bg/25"
            }`}
        >
            {label}
        </button>
    );

    const pill = (key: "sale" | "rent", label: string) => (
        <button
            type="button"
            onClick={() => setPurpose(key)}
            className={`rounded-full px-5 py-2 text-[13px] font-extrabold transition ${
                purpose === key
                    ? "bg-primary text-primary-fg"
                    : "border border-gray-200 bg-bg text-secondary hover:border-primary"
            }`}
        >
            {label}
        </button>
    );

    return (
        <section ref={box} className="relative isolate overflow-hidden">
            {/* ----- الخلفية: بتتحدد من إعداد "نمط الهيرو" في الهوية والألوان ----- */}
            <div className="absolute inset-0 -z-10">
                <img
                    src={poster}
                    alt=""
                    fetchPriority={variant === "webgl" ? "low" : "high"}
                    className="h-full w-full object-cover"
                />

                {/* video: الفيديو بيتحمّل كسول فوق الصورة · static: الصورة بس */}
                {variant === "video" && video && playVideo && (
                    <video
                        src={video}
                        poster={poster}
                        autoPlay
                        muted
                        loop
                        playsInline
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                )}

                {/* webgl: شادر بيقرا ألوانه من توكنز الثيم */}
                {variant === "webgl" && (
                    <Suspense fallback={null}>
                        <HeroWebGL />
                    </Suspense>
                )}
                {/* تعتيم بلون الهوية عشان النص يفضل مقروء.
                    مع الشادر بيبقى أخف — هو أصلًا بيرسم على الخلفية الداكنة،
                    فالتعتيم الكامل كان بيمسح اللي رسمه */}
                <div
                    className={
                        variant === "webgl"
                            ? "absolute inset-0 bg-gradient-to-b from-bg-dark/35 via-transparent to-bg-dark/55"
                            : "absolute inset-0 bg-gradient-to-b from-bg-dark/85 via-bg-dark/70 to-bg-dark/90"
                    }
                />
            </div>

            <div className="mx-auto max-w-7xl px-4 pb-14 pt-20 md:pt-28">
                <div className="text-center">
                    <h1 className="text-4xl font-black leading-[1.25] text-white md:text-6xl">
                        {t.h1a} <span className="text-primary">{t.h1b}</span>
                    </h1>
                    <p className="mx-auto mt-5 max-w-2xl text-base leading-[1.9] text-white/80 md:text-lg">{t.sub}</p>
                </div>

                {/* ----- كارت البحث ----- */}
                <div className="mx-auto mt-10 max-w-5xl">
                    <div className="flex gap-1.5">
                        {tabBtn("property", t.tabProperty)}
                        {tabBtn("project", t.tabProject)}
                    </div>

                    <form
                        action={`/${locale}/${tab === "project" ? "compounds" : "properties"}`}
                        className="rounded-2xl rounded-ss-none bg-bg p-5 shadow-[0_20px_60px_rgba(11,18,32,0.28)]"
                    >
                        <div className="flex flex-wrap items-center gap-2.5">
                            {pill("sale", t.sale)}
                            {pill("rent", t.rent)}
                            <input type="hidden" name="purpose" value={purpose} />
                        </div>

                        <div className="mt-4 grid gap-3 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
                            <label className="flex flex-col gap-2">
                                <span className="text-xs font-extrabold text-secondary">{t.qLabel}</span>
                                <input
                                    name="q"
                                    type="text"
                                    placeholder={tab === "project" ? t.qPlaceholderProject : t.qPlaceholder}
                                    className="w-full rounded-lg border border-gray-200 bg-bg px-4 py-3 text-sm font-bold text-text placeholder:font-medium placeholder:text-muted"
                                />
                            </label>

                            <label className="flex flex-col gap-2">
                                <span className="text-xs font-extrabold text-secondary">{t.typeLabel}</span>
                                <select
                                    name="type"
                                    className="rounded-lg border border-gray-200 bg-bg px-4 py-3 text-sm font-bold text-text"
                                >
                                    <option value="">{t.typeAll}</option>
                                    {options.types.map((v) => (
                                        <option key={v}>{v}</option>
                                    ))}
                                </select>
                            </label>

                            <label className="flex flex-col gap-2">
                                <span className="text-xs font-extrabold text-secondary">{t.locLabel}</span>
                                <select
                                    name="location"
                                    className="rounded-lg border border-gray-200 bg-bg px-4 py-3 text-sm font-bold text-text"
                                >
                                    <option value="">{t.locAll}</option>
                                    {options.locations.map((v) => (
                                        <option key={v}>{v}</option>
                                    ))}
                                </select>
                            </label>

                            <button
                                type="submit"
                                className="flex items-center justify-center gap-2 rounded-brand bg-primary px-8 py-3.5 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover"
                            >
                                <Search size={16} />
                                {t.search}
                            </button>
                        </div>
                    </form>
                </div>

                {/* ----- الأرقام + مساعدة ----- */}
                <div className="mt-10 flex flex-wrap items-center justify-center gap-x-10 gap-y-5">
                    {options.stats.map((s) => (
                        <div key={s.label} className="flex items-baseline gap-2">
                            <span className="text-3xl font-black text-primary" dir="ltr">
                                <CountUp value={s.value} />
                                {s.suffix}
                            </span>
                            <span className="text-sm font-bold text-white/75">{s.label}</span>
                        </div>
                    ))}

                    <span className="hidden h-8 w-px bg-white/25 md:block" aria-hidden />

                    <a
                        href={wa ? `https://wa.me/${wa}` : `/${locale}/contact`}
                        {...(wa ? { target: "_blank", rel: "noreferrer" } : {})}
                        className="flex items-center gap-2 text-sm font-extrabold text-white transition hover:text-primary"
                    >
                        <Headset size={17} className="text-primary" />
                        <span className="text-white/75">{t.helpTitle}</span>
                        <span className="underline underline-offset-4">{t.helpCta}</span>
                    </a>
                </div>
            </div>
        </section>
    );
}
