import { Link, usePage } from "@inertiajs/react";
import { MapPin } from "lucide-react";
import type { Compound, SharedProps } from "@/lib/types";

export default function CompoundCard({ c, ar }: { c: Compound; ar: boolean; wa?: string }) {
    const { locale } = usePage<SharedProps>().props;

    return (
        <article className="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-bg transition duration-200 hover:-translate-y-1 hover:border-primary/50 hover:shadow-[0_12px_30px_rgba(11,18,32,0.07)]">
            <div className="relative h-44 overflow-hidden bg-surface">
                <img
                    src={c.image}
                    alt={c.name}
                    loading="lazy"
                    className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                {c.new && (
                    <span className="absolute start-3 top-3 rounded-full bg-primary px-3 py-2 text-[11px] font-extrabold text-primary-fg">
                        {ar ? "إطلاق جديد" : "New launch"}
                    </span>
                )}
            </div>

            <div className="flex flex-1 flex-col gap-2 p-4">
                <h3 className="text-lg font-extrabold leading-relaxed text-secondary">{c.name}</h3>

                <div className="flex items-center gap-2 text-xs font-bold text-muted">
                    <MapPin size={13} className="shrink-0 text-primary" />
                    <span>
                        {c.area} · {c.developer}
                    </span>
                </div>

                <div className="mt-1 grid grid-cols-[1fr_1px_1fr_1px_1fr] rounded-xl bg-surface px-2 py-4">
                    <div className="flex flex-col items-center gap-1">
                        <span className="text-[11px] font-bold text-muted">{ar ? "يبدأ من" : "From"}</span>
                        <span className="text-sm font-extrabold text-primary" dir="ltr">
                            {c.starting}
                        </span>
                    </div>
                    <div className="bg-gray-200" />
                    <div className="flex flex-col items-center gap-1">
                        <span className="text-[11px] font-bold text-muted">{ar ? "مقدم" : "Down"}</span>
                        <span className="text-sm font-extrabold text-secondary" dir="ltr">
                            {c.down}
                        </span>
                    </div>
                    <div className="bg-gray-200" />
                    <div className="flex flex-col items-center gap-1">
                        <span className="text-[11px] font-bold text-muted">{ar ? "تقسيط" : "Plan"}</span>
                        <span className="text-sm font-extrabold text-secondary">{c.years}</span>
                    </div>
                </div>

                <Link
                    href={`/${locale}/compounds`}
                    className="mt-auto block rounded-brand border-2 border-primary py-3 text-center text-[13px] font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg"
                >
                    {ar ? "تفاصيل الكمبوند" : "Compound details"}
                </Link>
            </div>
        </article>
    );
}
