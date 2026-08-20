import { useEffect, useState } from "react";
import { isVideo } from "@/lib/media";

/**
 * معرض صور صفحات العرض — صورة كبيرة وتحتها مصغّرات.
 * بيتعامل مع الفيديو زي الصورة عشان مكتبة الميديا بتقبل الاتنين.
 */
export default function Gallery({ items, alt }: { items: string[]; alt: string }) {
    const [active, setActive] = useState(0);

    // لو العنصر اتغيّر (تنقّل Inertia لعقار تاني) نرجّع لأول صورة
    useEffect(() => setActive(0), [items]);

    if (items.length === 0) return null;

    const current = items[Math.min(active, items.length - 1)];

    return (
        <div className="flex flex-col gap-3">
            <div className="overflow-hidden rounded-2xl bg-surface">
                {isVideo(current) ? (
                    <video src={current} controls className="aspect-[16/10] w-full object-cover" />
                ) : (
                    <img
                        src={current}
                        alt={alt}
                        fetchPriority="high"
                        className="aspect-[16/10] w-full object-cover"
                    />
                )}
            </div>

            {items.length > 1 && (
                <div className="flex flex-wrap gap-2">
                    {items.map((src, i) => (
                        <button
                            key={`${src}-${i}`}
                            type="button"
                            onClick={() => setActive(i)}
                            aria-label={`${alt} — ${i + 1}`}
                            aria-current={i === active}
                            className={`h-16 w-24 shrink-0 overflow-hidden rounded-xl border-2 transition ${
                                i === active ? "border-primary" : "border-transparent opacity-70 hover:opacity-100"
                            }`}
                        >
                            {isVideo(src) ? (
                                <video src={src} muted className="h-full w-full object-cover" />
                            ) : (
                                <img src={src} alt="" loading="lazy" className="h-full w-full object-cover" />
                            )}
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
