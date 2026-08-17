import { useEffect, useRef, useState } from "react";

/** عدّاد متحرك للإحصائيات — بيبدأ لما يظهر في الشاشة، وبيحافظ على أي لاحقة زي + */
export default function CountUp({ value, duration = 1400 }: { value: string; duration?: number }) {
    const ref = useRef<HTMLSpanElement>(null);
    const [display, setDisplay] = useState(value);

    useEffect(() => {
        const match = value.match(/^([\d,]+)(.*)$/);
        if (!match || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            setDisplay(value);
            return;
        }
        const target = parseInt(match[1].replace(/,/g, ""), 10);
        const suffix = match[2] ?? "";
        setDisplay(`0${suffix}`);

        const el = ref.current;
        if (!el) return;

        const io = new IntersectionObserver(
            ([entry]) => {
                if (!entry.isIntersecting) return;
                io.disconnect();
                const start = performance.now();
                const tick = (now: number) => {
                    const p = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - p, 3);
                    setDisplay(`${Math.round(target * eased).toLocaleString()}${suffix}`);
                    if (p < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
            },
            { threshold: 0.4 },
        );
        io.observe(el);
        return () => io.disconnect();
    }, [value, duration]);

    return <span ref={ref}>{display}</span>;
}
