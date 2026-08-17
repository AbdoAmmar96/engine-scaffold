import { usePage } from "@inertiajs/react";
import PropertyCard from "@/Components/site/PropertyCard";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Property, SharedProps } from "@/lib/types";

/**
 * Properties v0 — قالب صفحة الليستنج.
 * البيانات تجريبية من DemoContent — المرحلة 4 بتوصلها بموديل Properties
 * وبتشغّل الفلاتر server-side بنفس شكل الـ props.
 */
export default function Properties({ properties }: { properties: Property[] }) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const wa = settings.contact?.whatsapp;

    return (
        <SiteLayout>
            <section className="border-b border-gray-100 bg-surface">
                <div className="mx-auto max-w-7xl px-4 py-12">
                    <h1 className="text-3xl text-secondary md:text-4xl">{ar ? "العقارات" : "Properties"}</h1>
                    <p className="mt-2 text-sm text-muted">
                        {ar
                            ? "بيانات تجريبية للعرض — الفلاتر بتتفعّل مع موديول العقارات في المرحلة 4."
                            : "Demo data for preview — filters go live with the Properties module in Phase 4."}
                    </p>

                    {/* شريط الفلاتر (UI) */}
                    <div className="mt-6 grid gap-3 rounded-2xl border border-gray-100 bg-bg p-4 sm:grid-cols-2 lg:grid-cols-5">
                        <select className="rounded-lg border border-gray-200 bg-bg px-3 py-2.5 text-sm text-secondary outline-none focus:border-primary">
                            <option>{ar ? "الغرض: الكل" : "Purpose: all"}</option>
                            <option>{ar ? "بيع" : "Sale"}</option>
                            <option>{ar ? "إيجار" : "Rent"}</option>
                        </select>
                        <select className="rounded-lg border border-gray-200 bg-bg px-3 py-2.5 text-sm text-secondary outline-none focus:border-primary">
                            <option>{ar ? "النوع: الكل" : "Type: all"}</option>
                            <option>{ar ? "شقق" : "Apartments"}</option>
                            <option>{ar ? "فلل" : "Villas"}</option>
                            <option>{ar ? "شاليهات" : "Chalets"}</option>
                        </select>
                        <select className="rounded-lg border border-gray-200 bg-bg px-3 py-2.5 text-sm text-secondary outline-none focus:border-primary">
                            <option>{ar ? "المنطقة: الكل" : "Area: all"}</option>
                            <option>{ar ? "القاهرة الجديدة" : "New Cairo"}</option>
                            <option>{ar ? "الشيخ زايد" : "Sheikh Zayed"}</option>
                            <option>{ar ? "الساحل الشمالي" : "North Coast"}</option>
                        </select>
                        <input
                            placeholder={ar ? "أقصى سعر (EGP)" : "Max price (EGP)"}
                            className="rounded-lg border border-gray-200 bg-bg px-3 py-2.5 text-sm outline-none focus:border-primary"
                        />
                        <button className="rounded-brand bg-primary px-4 py-2.5 text-sm font-extrabold text-primary-fg hover:bg-primary-hover">
                            {ar ? "بحث" : "Search"}
                        </button>
                    </div>
                </div>
            </section>

            <section className="bg-bg">
                <div className="mx-auto max-w-7xl px-4 py-14">
                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {properties.map((p, i) => (
                            <Reveal key={p.id} delay={i * 90}>
                                <PropertyCard p={p} ar={ar} wa={wa} />
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>
        </SiteLayout>
    );
}
