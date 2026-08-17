import { Bath, BedDouble, MapPin, Ruler } from "lucide-react";
import type { Property } from "@/lib/types";

export default function PropertyCard({ p, ar, wa }: { p: Property; ar: boolean; wa?: string }) {
    const isRent = p.purpose === "إيجار" || p.purpose === "Rent";

    return (
        <article className="group overflow-hidden rounded-2xl border border-gray-100 bg-bg transition hover:-translate-y-1 hover:border-primary/50 hover:shadow-md">
            {/* صورة العقار — بتيجي من الـ Media Manager في المرحلة 2 */}
            <div className="relative flex h-44 items-center justify-center bg-surface">
                <span className="text-4xl font-extrabold text-gray-200">{p.ref}</span>
                <span
                    className={`absolute start-3 top-3 rounded-full px-3 py-1 text-[11px] font-extrabold ${
                        isRent ? "bg-secondary text-white" : "bg-primary text-primary-fg"
                    }`}
                >
                    {p.purpose}
                </span>
            </div>

            <div className="p-5">
                <h3 className="line-clamp-2 text-sm font-extrabold leading-relaxed text-secondary">{p.title}</h3>
                <div className="mt-2 flex items-center gap-1.5 text-xs text-muted">
                    <MapPin size={13} />
                    {p.area}
                </div>
                <div className="mt-3 text-lg font-extrabold text-primary" dir="ltr">
                    {p.price}
                </div>
                <div className="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-xs font-bold text-muted">
                    {p.beds > 0 && (
                        <span className="flex items-center gap-1">
                            <BedDouble size={14} /> {p.beds}
                        </span>
                    )}
                    <span className="flex items-center gap-1">
                        <Bath size={14} /> {p.baths}
                    </span>
                    <span className="flex items-center gap-1">
                        <Ruler size={14} /> {p.size} {ar ? "م²" : "m²"}
                    </span>
                </div>
                {wa && (
                    <a
                        href={`https://wa.me/${wa}?text=${encodeURIComponent((ar ? "مهتم بالعقار: " : "Interested in property: ") + p.ref)}`}
                        target="_blank"
                        rel="noreferrer"
                        className="mt-4 block rounded-brand border-2 border-primary py-2 text-center text-xs font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg"
                    >
                        {ar ? "استفسر واتساب" : "Ask on WhatsApp"}
                    </a>
                )}
            </div>
        </article>
    );
}
