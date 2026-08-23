import { useEffect, useRef, useState, type ReactNode } from "react";

/** أنيميشن ظهور عند التمرير — IntersectionObserver بدون أي مكتبات، وبيحترم reduced-motion */
export default function Reveal({
    children,
    delay = 0,
    className = "",
}: {
    children: ReactNode;
    delay?: number;
    className?: string;
}) {
    const ref = useRef<HTMLDivElement>(null);
    const [shown, setShown] = useState(false);

    useEffect(() => {
        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            setShown(true);
            return;
        }
        const el = ref.current;
        if (!el) return;
        const io = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    setShown(true);
                    io.disconnect();
                }
            },
            { threshold: 0.15 },
        );
        io.observe(el);
        return () => io.disconnect();
    }, []);

    return (
        <div
            ref={ref}
            // min-w-0: العنصر ده بيتحط جوّه جريد وفلكس في كل الموقع، وعنصر
            // الجريد بيرفض افتراضيًا يقل عن عرض محتواه. من غيرها كارت فيه
            // نص إنجليزي طويل كان بيمدّ الصفحة كلها — والعربي مكانش بيبان
            // فيه العطل لأن كلماته أقصر، فالطفح ظهر في /en بس.
            className={`min-w-0 ${className}`}
            style={{
                opacity: shown ? 1 : 0,
                transform: shown ? "translateY(0)" : "translateY(28px)",
                transition: `opacity 0.7s ease ${delay}ms, transform 0.7s ease ${delay}ms`,
            }}
        >
            {children}
        </div>
    );
}
