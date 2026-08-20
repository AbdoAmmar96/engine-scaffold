import { Link, usePage } from "@inertiajs/react";
import {
    ArrowLeft,
    Bath,
    BedDouble,
    Building2,
    CalendarCheck,
    Check,
    Hash,
    MapPin,
    MessageCircle,
    Phone,
    Ruler,
    Tag,
} from "lucide-react";
import type { ReactNode } from "react";
import Gallery from "@/Components/site/Gallery";
import PageHero from "@/Components/site/PageHero";
import PropertyCard from "@/Components/site/PropertyCard";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Property, PropertyDetail, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "عقارات",
        back: "كل العقارات",
        specs: "بيانات الوحدة",
        about: "عن الوحدة",
        features: "المميزات",
        related: "عقارات ممكن تناسبك",
        type: "النوع",
        purpose: "الغرض",
        beds: "غرف النوم",
        baths: "الحمامات",
        size: "المساحة",
        area: "المنطقة",
        ref: "كود الوحدة",
        project: "المشروع",
        delivery: "التسليم",
        price: "السعر",
        wa: "استفسر واتساب",
        call: "اتصل بنا",
        form: "اطلب معاينة",
        note: "الأسعار والمساحات مذكورة كما وردت من المطوّر/المالك، وبتتأكّد وقت المعاينة.",
        projectCta: "شوف المشروع كامل",
        meter: "م²",
    },
    en: {
        crumb: "Properties",
        back: "All properties",
        specs: "Unit details",
        about: "About this unit",
        features: "Features",
        related: "You may also like",
        type: "Type",
        purpose: "Purpose",
        beds: "Bedrooms",
        baths: "Bathrooms",
        size: "Size",
        area: "Area",
        ref: "Reference",
        project: "Project",
        delivery: "Delivery",
        price: "Price",
        wa: "Ask on WhatsApp",
        call: "Call us",
        form: "Request a viewing",
        note: "Prices and sizes are stated as provided by the developer/owner, and confirmed at viewing.",
        projectCta: "See the full project",
        meter: "m²",
    },
};

export default function PropertyPage({
    property,
    related,
}: {
    property: PropertyDetail;
    related: Property[];
}) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const t = copy[locale] ?? copy.ar;

    const wa = settings.contact?.whatsapp;
    const phone = settings.contact?.phone;
    const isRent = property.purpose === "إيجار" || property.purpose === "Rent";

    const spec = (icon: ReactNode, label: string, value: ReactNode) => (
        <div className="flex items-start gap-3 rounded-xl bg-surface p-4">
            <span className="mt-0.5 shrink-0 text-primary">{icon}</span>
            <span className="flex min-w-0 flex-col gap-1">
                <span className="text-[11px] font-bold text-muted">{label}</span>
                <span className="text-sm font-extrabold text-secondary">{value}</span>
            </span>
        </div>
    );

    return (
        <SiteLayout>
            <PageHero
                bg={property.image}
                crumb={t.crumb}
                crumbHref={`/${locale}/properties`}
                title={property.title}
                desc={property.area}
            >
                <div className="flex flex-wrap items-center gap-2.5 text-[12px] font-extrabold">
                    <span
                        className={`rounded-full px-3 py-1.5 ${
                            isRent ? "bg-secondary text-white" : "bg-primary text-primary-fg"
                        }`}
                    >
                        {property.purpose}
                    </span>
                    {property.type && (
                        <span className="rounded-full bg-white/10 px-3 py-1.5 text-white/85">{property.type}</span>
                    )}
                    {property.ref && (
                        <span className="flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-white/85">
                            <Hash size={12} className="text-primary" />
                            <span dir="ltr">{property.ref}</span>
                        </span>
                    )}
                </div>
            </PageHero>

            <section className="bg-bg px-4 py-12">
                <div className="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[minmax(0,1fr)_21rem]">
                    {/* ---------- العمود الأساسي ---------- */}
                    <div className="flex min-w-0 flex-col gap-8">
                        <Reveal>
                            <Gallery items={property.gallery} alt={property.title} />
                        </Reveal>

                        <Reveal>
                            <div>
                                <h2 className="mb-4 text-xl font-extrabold text-secondary">{t.specs}</h2>
                                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    {property.type && spec(<Tag size={17} />, t.type, property.type)}
                                    {spec(<CalendarCheck size={17} />, t.purpose, property.purpose)}
                                    {property.beds > 0 && (
                                        <>{spec(<BedDouble size={17} />, t.beds, <span dir="ltr">{property.beds}</span>)}</>
                                    )}
                                    {property.baths > 0 && (
                                        <>{spec(<Bath size={17} />, t.baths, <span dir="ltr">{property.baths}</span>)}</>
                                    )}
                                    {property.size > 0 &&
                                        spec(
                                            <Ruler size={17} />,
                                            t.size,
                                            <span className="flex items-center gap-1.5">
                                                <span dir="ltr">{property.size}</span>
                                                <span>{t.meter}</span>
                                            </span>,
                                        )}
                                    {property.area && spec(<MapPin size={17} />, t.area, property.area)}
                                    {property.ref &&
                                        spec(<Hash size={17} />, t.ref, <span dir="ltr">{property.ref}</span>)}
                                    {property.compound &&
                                        spec(<Building2 size={17} />, t.project, property.compound.name)}
                                    {property.compound?.delivery &&
                                        spec(
                                            <CalendarCheck size={17} />,
                                            t.delivery,
                                            <span dir="auto">{property.compound.delivery}</span>,
                                        )}
                                </div>
                            </div>
                        </Reveal>

                        {property.description && (
                            <Reveal>
                                <div>
                                    <h2 className="mb-3 text-xl font-extrabold text-secondary">{t.about}</h2>
                                    <div className="flex flex-col gap-4">
                                        {property.description
                                            .split("\n")
                                            .map((line) => line.trim())
                                            .filter(Boolean)
                                            .map((line, i) => (
                                                <p key={i} className="text-[15px] leading-[2.1] text-text">
                                                    {line}
                                                </p>
                                            ))}
                                    </div>
                                </div>
                            </Reveal>
                        )}

                        {property.features.length > 0 && (
                            <Reveal>
                                <div>
                                    <h2 className="mb-4 text-xl font-extrabold text-secondary">{t.features}</h2>
                                    <ul className="grid gap-2.5 sm:grid-cols-2">
                                        {property.features.map((f, i) => (
                                            <li key={i} className="flex items-start gap-2.5 text-[14px] text-text">
                                                <span className="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                                    <Check size={12} />
                                                </span>
                                                {f}
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </Reveal>
                        )}

                        <Link
                            href={`/${locale}/properties`}
                            className="flex w-fit items-center gap-2 text-[13px] font-extrabold text-secondary transition hover:text-primary"
                        >
                            <ArrowLeft size={15} className="text-primary ltr:rotate-180" />
                            {t.back}
                        </Link>
                    </div>

                    {/* ---------- العمود الجانبي ---------- */}
                    <aside className="flex flex-col gap-5 lg:sticky lg:top-24 lg:self-start">
                        <div className="rounded-2xl border border-gray-100 bg-bg p-6 shadow-[0_4px_18px_rgba(11,18,32,0.05)]">
                            <span className="text-[11px] font-bold text-muted">{t.price}</span>
                            <div className="mt-1 text-2xl font-black text-primary" dir="ltr">
                                {property.price}
                            </div>

                            <div className="mt-5 flex flex-col gap-2.5">
                                {wa && (
                                    <a
                                        href={`https://wa.me/${wa}?text=${encodeURIComponent(
                                            (ar ? "مهتم بالعقار: " : "Interested in property: ") +
                                                property.title +
                                                (property.ref ? ` (${property.ref})` : ""),
                                        )}`}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="flex items-center justify-center gap-2 rounded-brand bg-primary py-3 text-[13px] font-extrabold text-primary-fg transition hover:opacity-90"
                                    >
                                        <MessageCircle size={16} />
                                        {t.wa}
                                    </a>
                                )}

                                {phone && (
                                    <a
                                        href={`tel:${phone}`}
                                        className="flex items-center justify-center gap-2 rounded-brand border-2 border-primary py-3 text-[13px] font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg"
                                    >
                                        <Phone size={16} />
                                        {t.call}
                                    </a>
                                )}

                                <Link
                                    href={`/${locale}/contact`}
                                    className="flex items-center justify-center gap-2 rounded-brand border border-gray-200 py-3 text-[13px] font-extrabold text-secondary transition hover:border-primary hover:text-primary"
                                >
                                    {t.form}
                                </Link>
                            </div>

                            <p className="mt-4 text-[11px] leading-[1.9] text-muted">{t.note}</p>
                        </div>

                        {property.compound && property.compound.slug && (
                            <Link
                                href={`/${locale}/compounds/${property.compound.slug}`}
                                className="group rounded-2xl bg-bg-dark p-6 transition hover:bg-secondary"
                            >
                                <span className="flex items-center gap-2 text-[11px] font-bold text-white/60">
                                    <Building2 size={13} className="text-primary" />
                                    {t.project}
                                </span>
                                <h3 className="mt-2 text-base font-extrabold leading-[1.7] text-white">
                                    {property.compound.name}
                                </h3>
                                {property.compound.developer && (
                                    <p className="mt-1 text-[12px] font-bold text-white/60">
                                        {property.compound.developer}
                                    </p>
                                )}
                                <span className="mt-4 flex items-center gap-2 text-[13px] font-extrabold text-primary">
                                    {t.projectCta}
                                    <ArrowLeft size={15} className="ltr:rotate-180" />
                                </span>
                            </Link>
                        )}
                    </aside>
                </div>
            </section>

            {related.length > 0 && (
                <section className="bg-surface px-4 py-14">
                    <div className="mx-auto max-w-7xl">
                        <h2 className="mb-6 text-2xl font-extrabold text-secondary">{t.related}</h2>
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {related.map((p, i) => (
                                <Reveal key={p.id} delay={i * 80}>
                                    <PropertyCard p={p} ar={ar} wa={wa} />
                                </Reveal>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </SiteLayout>
    );
}
