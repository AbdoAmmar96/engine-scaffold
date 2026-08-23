import { usePage } from "@inertiajs/react";
import { ArrowLeft, Bath, BedDouble, Megaphone, Ruler } from "lucide-react";
import type { SharedProps } from "@/lib/types";

export interface Ad {
    adId: number;
    kind: "property" | "compound";
    /** رابط التتبّع — بيعدّ الضغطة وبيحوّل للصفحة الحقيقية */
    url: string;
    title?: string;
    name?: string;
    image: string;
    area?: string;
    price?: string;
    starting?: string;
    beds?: number;
    baths?: number;
    size?: number;
    developer?: string;
}

const copy = {
    ar: { label: "إعلان مميّز", open: "افتح" },
    en: { label: "Sponsored", open: "Open" },
};

/**
 * شريط المساحات الإعلانية.
 *
 * شكله مختلف عن كروت النتايج عن قصد ومكتوب عليه «إعلان مميّز» — الزائر
 * لازم يفرّق بين اللي طلع بالبحث واللي مدفوع، وإلا الثقة في النتايج كلها
 * بتقل.
 *
 * الروابط <a> عادية مش <Link>: بتعدّي على راوت التتبّع في السيرفر،
 * فالضغطة بتتحسب حتى لو الجافاسكربت واقع.
 */
export default function AdStrip({ ads, compact = false }: { ads: Ad[]; compact?: boolean }) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    if (!ads || ads.length === 0) return null;

    const spec = (value: number | undefined, Icon: typeof Ruler, suffix = "") =>
        value ? (
            <span className="flex items-center gap-1 text-[11px] font-bold text-muted">
                <Icon size={12} />
                <span dir="auto">
                    {value}
                    {suffix}
                </span>
            </span>
        ) : null;

    return (
        <section className={compact ? "" : "bg-bg px-4 py-8"}>
            <div className={compact ? "" : "mx-auto max-w-7xl"}>
                <p className="mb-3 flex items-center gap-2 text-[11px] font-extrabold tracking-wide text-muted">
                    <Megaphone size={13} className="text-primary" />
                    {t.label}
                </p>

                <ul className={`grid gap-4 ${compact ? "" : "sm:grid-cols-2 lg:grid-cols-3"}`}>
                    {ads.map((ad) => (
                        <li key={ad.adId}>
                            <a
                                href={ad.url}
                                className="group flex gap-3 overflow-hidden rounded-2xl border border-primary/25 bg-surface p-3 transition hover:border-primary hover:shadow-[0_6px_22px_rgba(11,18,32,0.07)]"
                            >
                                <img
                                    src={ad.image}
                                    alt=""
                                    loading="lazy"
                                    className="h-20 w-24 shrink-0 rounded-xl object-cover"
                                />

                                <div className="flex min-w-0 flex-1 flex-col justify-center gap-1">
                                    <h3 className="truncate text-[13px] font-extrabold text-secondary">
                                        {ad.title ?? ad.name}
                                    </h3>

                                    {ad.area && <p className="truncate text-[11px] font-bold text-muted">{ad.area}</p>}

                                    <p dir="auto" className="text-[12px] font-extrabold text-primary">
                                        {ad.price ?? ad.starting}
                                    </p>

                                    <div className="flex flex-wrap items-center gap-x-3">
                                        {spec(ad.beds, BedDouble)}
                                        {spec(ad.baths, Bath)}
                                        {spec(ad.size, Ruler, " م²")}
                                    </div>
                                </div>

                                <ArrowLeft
                                    size={15}
                                    className="mt-1 shrink-0 self-start text-muted transition group-hover:text-primary rtl:rotate-180"
                                />
                            </a>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
