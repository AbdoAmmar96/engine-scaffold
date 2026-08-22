import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, Bath, BedDouble, Building2, MapPin, Ruler, Star } from "lucide-react";
import FavoriteButton from "@/Components/site/FavoriteButton";
import type { ReactNode } from "react";
import type { Property, SharedProps } from "@/lib/types";

export default function PropertyCard({ p, ar, wa }: { p: Property; ar: boolean; wa?: string }) {
    const { locale } = usePage<SharedProps>().props;
    const isRent = p.purpose === "إيجار" || p.purpose === "Rent";
    const href = p.slug ? `/${locale}/properties/${p.slug}` : null;

    // الكارت كله لينك لصفحة العقار — زرار الواتساب بره اللينك
    // عشان مينفعش <a> جوه <a>
    const body = (
        <>
            <div className="relative h-44 overflow-hidden bg-surface">
                <img
                    src={p.image}
                    alt={p.title}
                    loading="lazy"
                    className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <span className="absolute start-3 top-3 flex flex-wrap items-center gap-2">
                    <span
                        className={`rounded-full px-3 py-2 text-[11px] font-extrabold ${
                            isRent ? "bg-secondary text-white" : "bg-primary text-primary-fg"
                        }`}
                    >
                        {p.purpose}
                    </span>
                    {p.featured && (
                        <span className="flex items-center gap-1 rounded-full bg-bg-dark px-2.5 py-2 text-[11px] font-extrabold text-primary">
                            <Star size={11} fill="currentColor" />
                            {ar ? "مميّز" : "Featured"}
                        </span>
                    )}
                </span>

                {href && (
                    <span className="absolute inset-0 flex items-center justify-center bg-bg-dark/45 opacity-0 transition duration-300 group-hover:opacity-100">
                        <span className="flex items-center gap-2 rounded-brand bg-primary px-4 py-2.5 text-[13px] font-extrabold text-primary-fg">
                            {ar ? "عرض التفاصيل" : "View details"}
                            <ArrowLeft size={15} className="ltr:rotate-180" />
                        </span>
                    </span>
                )}
            </div>

            <div className="flex flex-1 flex-col gap-2 p-4">
                <h3 className="truncate text-[17px] font-extrabold leading-relaxed text-secondary transition group-hover:text-primary">
                    {p.title}
                </h3>

                <div className="flex items-center gap-2 text-xs font-bold text-muted">
                    <MapPin size={13} className="shrink-0 text-primary" />
                    <span className="truncate">{p.area}</span>
                    {p.developer && (
                        <>
                            <span className="text-gray-300">·</span>
                            <Building2 size={13} className="shrink-0 text-primary" />
                            <span className="truncate">{p.developer}</span>
                        </>
                    )}
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
            </div>
        </>
    );

    const shell = (children: ReactNode) =>
        href ? (
            <Link href={href} className="flex flex-1 flex-col">
                {children}
            </Link>
        ) : (
            <div className="flex flex-1 flex-col">{children}</div>
        );

    return (
        <article className="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-bg transition duration-200 hover:-translate-y-1 hover:border-primary/50 hover:shadow-[0_12px_30px_rgba(11,18,32,0.07)]">
            {/* بره اللينك: <button> جوه <a> مش HTML صالح */}
            <div className="absolute end-3 top-3 z-10">
                <FavoriteButton propertyId={p.id} />
            </div>

            {shell(body)}

            {wa && (
                <a
                    href={`https://wa.me/${wa}?text=${encodeURIComponent((ar ? "مهتم بالعقار: " : "Interested in property: ") + p.ref)}`}
                    target="_blank"
                    rel="noreferrer"
                    className="mx-4 mb-4 block rounded-brand border-2 border-primary py-3 text-center text-[13px] font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg"
                >
                    {ar ? "استفسر واتساب" : "Ask on WhatsApp"}
                </a>
            )}
        </article>
    );
}
