import { usePage } from "@inertiajs/react";
import CompoundCard from "@/Components/site/CompoundCard";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Compound, SharedProps } from "@/lib/types";

export default function Compounds({ compounds }: { compounds: Compound[] }) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const wa = settings.contact?.whatsapp;

    return (
        <SiteLayout>
            <section className="border-b border-gray-100 bg-surface">
                <div className="mx-auto max-w-7xl px-4 py-12">
                    <h1 className="text-3xl text-secondary md:text-4xl">{ar ? "الكمبوندات" : "Compounds"}</h1>
                    <p className="mt-2 text-sm text-muted">
                        {ar
                            ? "بيانات تجريبية للعرض — موديول الكمبوندات الكامل في المرحلة 4."
                            : "Demo data for preview — the full Compounds module lands in Phase 4."}
                    </p>
                </div>
            </section>

            <section className="bg-bg">
                <div className="mx-auto max-w-7xl px-4 py-14">
                    <div className="grid gap-6 sm:grid-cols-2">
                        {compounds.map((c, i) => (
                            <Reveal key={c.id} delay={i * 90}>
                                <CompoundCard c={c} ar={ar} wa={wa} />
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>
        </SiteLayout>
    );
}
