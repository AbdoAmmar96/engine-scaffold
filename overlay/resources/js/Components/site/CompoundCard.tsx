import { Building2, MapPin } from "lucide-react";
import type { Compound } from "@/lib/types";

export default function CompoundCard({ c, ar, wa }: { c: Compound; ar: boolean; wa?: string }) {
    return (
        <article className="overflow-hidden rounded-2xl border border-gray-100 bg-bg transition hover:-translate-y-1 hover:border-primary/50 hover:shadow-md">
            <div className="relative flex h-40 items-center justify-center bg-surface">
                <Building2 size={44} className="text-gray-200" />
                {c.new && (
                    <span className="absolute start-3 top-3 rounded-full bg-primary px-3 py-1 text-[11px] font-extrabold text-primary-fg">
                        {ar ? "إطلاق جديد" : "New launch"}
                    </span>
                )}
            </div>

            <div className="p-5">
                <h3 className="text-lg font-extrabold text-secondary">{c.name}</h3>
                <div className="mt-1 text-xs font-bold text-muted">{c.developer}</div>
                <div className="mt-2 flex items-center gap-1.5 text-xs text-muted">
                    <MapPin size={13} />
                    {c.area}
                </div>

                <div className="mt-4 grid grid-cols-3 gap-2 rounded-xl bg-surface p-3 text-center">
                    <div>
                        <div className="text-[10px] font-bold text-muted">{ar ? "يبدأ من" : "Starting"}</div>
                        <div className="mt-0.5 text-xs font-extrabold text-secondary" dir="ltr">{c.starting}</div>
                    </div>
                    <div className="border-x border-gray-200">
                        <div className="text-[10px] font-bold text-muted">{ar ? "مقدم" : "Down"}</div>
                        <div className="mt-0.5 text-xs font-extrabold text-secondary">{c.down}</div>
                    </div>
                    <div>
                        <div className="text-[10px] font-bold text-muted">{ar ? "تقسيط" : "Installments"}</div>
                        <div className="mt-0.5 text-xs font-extrabold text-secondary">{c.years}</div>
                    </div>
                </div>

                {wa && (
                    <a
                        href={`https://wa.me/${wa}?text=${encodeURIComponent((ar ? "مهتم بمشروع: " : "Interested in project: ") + c.name)}`}
                        target="_blank"
                        rel="noreferrer"
                        className="mt-4 block rounded-brand bg-primary py-2.5 text-center text-xs font-extrabold text-primary-fg transition hover:bg-primary-hover"
                    >
                        {ar ? "اعرف التفاصيل" : "Get details"}
                    </a>
                )}
            </div>
        </article>
    );
}
