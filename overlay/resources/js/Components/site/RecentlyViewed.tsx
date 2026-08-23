import { usePage } from "@inertiajs/react";
import { History } from "lucide-react";
import { useEffect, useState } from "react";
import PropertyCard from "@/Components/site/PropertyCard";
import Reveal from "@/Components/site/Reveal";
import type { Property, SharedProps } from "@/lib/types";

const KEY = "bp.recently-viewed";
const LIMIT = 8;

const copy = {
    ar: { title: "شوهدت مؤخرًا", desc: "الوحدات التي فتحتها آخر مرة — أكمل من حيث توقّفت." },
    en: { title: "Recently viewed", desc: "The units you opened last — pick up where you left off." },
};

/** أرقام الوحدات المخزّنة في المتصفح — الأحدث الأول */
export function readRecent(): number[] {
    if (typeof window === "undefined") return [];

    try {
        const raw = JSON.parse(window.localStorage.getItem(KEY) ?? "[]");

        return Array.isArray(raw) ? raw.filter((n) => Number.isInteger(n)).slice(0, LIMIT) : [];
    } catch {
        // خزّن ممتلئ أو محتوى باظ — القايمة مش حاجة تستاهل تكسر الصفحة
        return [];
    }
}

/** بتتنادى من صفحة الوحدة */
export function pushRecent(id: number): void {
    if (typeof window === "undefined" || !Number.isInteger(id)) return;

    try {
        const next = [id, ...readRecent().filter((n) => n !== id)].slice(0, LIMIT);

        window.localStorage.setItem(KEY, JSON.stringify(next));
    } catch {
        // وضع التصفّح الخاص بيرمي هنا — بنعدّي من غير ما نوقف حاجة
    }
}

/**
 * «شوهدت مؤخرًا».
 *
 * المسجّل قايمته من السيرفر (بتمشي معاه بين الأجهزة)، والزائر من
 * localStorage — والأرقام بتروح السيرفر يرجّع بيها كروت متحدّثة بدل
 * ما نخزّن السعر في المتصفح ويقعد قديم.
 */
export default function RecentlyViewed({ properties = [] }: { properties?: Property[] }) {
    const { locale, auth, settings } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const [rows, setRows] = useState<Property[]>(properties);

    useEffect(() => {
        if (properties.length > 0) return;

        const ids = readRecent();

        if (ids.length === 0) return;

        let alive = true;

        fetch(`/${locale}/recently-viewed?ids=${ids.join(",")}`, { headers: { Accept: "application/json" } })
            .then((r) => (r.ok ? r.json() : { properties: [] }))
            .then((d) => alive && setRows(d.properties ?? []))
            .catch(() => undefined);

        return () => {
            alive = false;
        };
    }, [locale, properties.length, auth.user?.id]);

    if (rows.length === 0) return null;

    return (
        <section className="bg-surface px-4 py-16">
            <div className="mx-auto max-w-7xl">
                <span className="inline-flex items-center gap-2 rounded-full border border-primary/40 bg-primary/10 px-4 py-2 text-[11px] font-extrabold text-secondary">
                    <History size={13} />
                    {t.title}
                </span>

                <p className="mt-3 mb-6 text-sm text-muted">{t.desc}</p>

                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {rows.slice(0, 4).map((p, i) => (
                        <Reveal key={p.id} delay={Math.min(i, 4) * 70}>
                            <PropertyCard p={p} ar={locale === "ar"} wa={settings.contact?.whatsapp} />
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}
