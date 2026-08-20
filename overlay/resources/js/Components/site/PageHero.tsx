import { Link, usePage } from "@inertiajs/react";
import { ChevronLeft } from "lucide-react";
import type { ReactNode } from "react";
import type { SharedProps } from "@/lib/types";

/**
 * هيرو موحّد للصفحات الداخلية — خلفية صورة بتعتيم الهوية،
 * وفوقها مسار التنقّل والعنوان ووصف مختصر، مع مساحة اختيارية لعناصر إضافية.
 * نفس لغة الهيرو الرئيسي بس أقصر — الصفحة الداخلية محتاجة تبدأ بمحتواها بسرعة.
 */
export default function PageHero({
    bg,
    title,
    desc,
    crumb,
    crumbHref,
    children,
}: {
    bg: string;
    title: string;
    desc?: string;
    /** اسم الصفحة في مسار التنقّل — "الرئيسية · <crumb>" */
    crumb: string;
    /** لو الصفحة دي تحت صفحة تانية، الـ crumb بيبقى لينك ليها */
    crumbHref?: string;
    children?: ReactNode;
}) {
    const { locale } = usePage<SharedProps>().props;
    const ar = locale === "ar";

    return (
        <section className="relative isolate overflow-hidden">
            <div className="absolute inset-0 -z-10">
                <img src={bg} alt="" fetchPriority="high" className="h-full w-full object-cover" />
                <div className="absolute inset-0 bg-gradient-to-b from-bg-dark/88 via-bg-dark/78 to-bg-dark/92" />
            </div>

            <div className="mx-auto max-w-7xl px-4 py-14 md:py-16">
                <nav className="flex items-center gap-1.5 text-xs font-bold text-white/60">
                    <Link href={`/${locale}`} className="transition hover:text-primary">
                        {ar ? "الرئيسية" : "Home"}
                    </Link>
                    <ChevronLeft size={13} className="rtl:rotate-180" />
                    {crumbHref ? (
                        <Link href={crumbHref} className="transition hover:text-primary">
                            {crumb}
                        </Link>
                    ) : (
                        <span className="text-white/85">{crumb}</span>
                    )}
                </nav>

                <h1 className="mt-3 text-3xl font-black leading-[1.3] text-white md:text-5xl">{title}</h1>

                {desc && <p className="mt-3 max-w-2xl text-base leading-[1.9] text-white/75">{desc}</p>}

                {children && <div className="mt-6">{children}</div>}
            </div>
        </section>
    );
}
