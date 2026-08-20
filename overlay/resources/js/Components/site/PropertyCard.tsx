import { Bath, BedDouble, MapPin, Ruler } from "lucide-react";
import type { Property } from "@/lib/types";

export default function PropertyCard({ p, ar, wa }: { p: Property; ar: boolean; wa?: string }) {
    const isRent = p.purpose === "إيجار" || p.purpose === "Rent";

    return (
        <article className="group flex flex-col overflow-hidden rounded-2xl border border-gray-100 bg-bg transition duration-200 hover:-translate-y-1 hover:border-primary/50 hover:shadow-[0_12px_30px_rgba(11,18,32,0.07)]">
            <div className="relative h-44 overflow-hidden bg-surface">
                <img
                    src={p.image}
                    alt={p.title}
                    loading="lazy"
                    className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <span
                    className={`absolute start-3 top-3 rounded-full px-3 py-2 text-[11px] font-extrabold ${
                        isRent ? "bg-secondary text-white" : "bg-primary text-primary-fg"
                    }`}
                >
                    {p.purpose}
                </span>
            </div>

            <div className="flex flex-1 flex-col gap-2 p-4">
                <h3 className="truncate text-[17px] font-extrabold leading-relaxed text-secondary">{p.title}</h3>

                <div className="flex items-center gap-2 text-xs font-bold text-muted">
                    <MapPin size={13} className="shrink-0 text-primary" />
                    {p.area}
                </div>

                <div className="text-lg font-extrabold text-primary" dir="ltr">
                    {p.price}
                </div>

                <div className="h-px bg-gray-100" />

                <div className="flex items-center gap-[18px] text-xs font-bold text-muted">
                    {p.beds > 0 && (
                        <span className="flex items-center gap-2">
                            <BedDouble size={14} className="text-secondary" />
                            <span dir="ltr">{p.beds}</span>
                        </span>
                    )}
                    <span className="flex items-center gap-2">
                        <Bath size={14} className="text-secondary" />
                        <span dir="ltr">{p.baths}</span>
                    </span>
                    {/* الرقم والوحدة عنصرين منفصلين — لو اتلفّوا في dir="ltr" واحد بيتقلبوا في RTL */}
                    <span className="flex items-center gap-1.5">
                        <Ruler size={14} className="text-secondary" />
                        <span dir="ltr">{p.size}</span>
                        <span>{ar ? "م²" : "m²"}</span>
                    </span>
                </div>

                {wa && (
                    <a
                        href={`https://wa.me/${wa}?text=${encodeURIComponent((ar ? "مهتم بالعقار: " : "Interested in property: ") + p.ref)}`}
                        target="_blank"
                        rel="noreferrer"
                        className="mt-auto block rounded-brand border-2 border-primary py-3 text-center text-[13px] font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg"
                    >
                        {ar ? "استفسر واتساب" : "Ask on WhatsApp"}
                    </a>
                )}
            </div>
        </article>
    );
}
