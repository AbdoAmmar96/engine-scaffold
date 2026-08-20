import { useEffect, useRef, useState } from "react";

/**
 * إطار ميديا موحّد — بيرندر <video> لو المسار فيديو و<img> لو صورة.
 * القيمة جاية من الإعدادات (branding.hero_media / branding.process_media)،
 * فتقدر تبدّل الفيديو بصورة أو العكس من الداشبورد من غير أي تعديل كود.
 *
 * الفيديو تقيل (2.5 ميجا)، فبنأجّل تحميله:
 *   1. الـ poster بيظهر فورًا — الصفحة مبتستناش الفيديو
 *   2. الـ src مبيتحطش غير لما الإطار يقرب من الشاشة (200px قبلها)
 *   3. وحتى وقتها بنستنى الميتصفح يفضى (requestIdleCallback)
 *   4. مع prefers-reduced-motion بنكتفي بالـ poster ومنحمّلش الفيديو خالص
 */
export default function FrameMedia({
    src,
    poster,
    alt = "",
    ratio = "4 / 4.6",
    priority = false,
    className = "",
}: {
    src?: string;
    poster?: string;
    alt?: string;
    ratio?: string;
    /** صورة فوق طية الشاشة — بتتحمّل فورًا بأولوية عالية */
    priority?: boolean;
    className?: string;
}) {
    const box = useRef<HTMLDivElement>(null);
    const [load, setLoad] = useState(false);

    const isVideo = /\.(mp4|webm|ogv)(\?|$)/i.test(src ?? "");

    useEffect(() => {
        if (!isVideo || !box.current) return;

        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

        const idle = (cb: () => void) =>
            typeof window.requestIdleCallback === "function"
                ? window.requestIdleCallback(cb, { timeout: 2500 })
                : window.setTimeout(cb, 400);

        const io = new IntersectionObserver(
            ([e]) => {
                if (!e.isIntersecting) return;
                io.disconnect();
                idle(() => setLoad(true));
            },
            { rootMargin: "200px" },
        );

        io.observe(box.current);
        return () => io.disconnect();
    }, [isVideo, src]);

    if (!src) return null;

    return (
        <div
            ref={box}
            className={`overflow-hidden rounded-3xl border border-gray-200 bg-bg-dark ${className}`}
            style={{ aspectRatio: ratio }}
        >
            {isVideo ? (
                load ? (
                    <video
                        src={src}
                        poster={poster}
                        autoPlay
                        muted
                        loop
                        playsInline
                        className="block h-full w-full object-cover"
                    />
                ) : (
                    // نفس مكان الفيديو بالظبط — مفيش قفزة في التخطيط
                    poster && (
                        <img
                            src={poster}
                            alt={alt}
                            fetchPriority={priority ? "high" : "auto"}
                            className="block h-full w-full object-cover"
                        />
                    )
                )
            ) : (
                <img
                    src={src}
                    alt={alt}
                    loading={priority ? "eager" : "lazy"}
                    fetchPriority={priority ? "high" : "auto"}
                    className="block h-full w-full object-cover"
                />
            )}
        </div>
    );
}
