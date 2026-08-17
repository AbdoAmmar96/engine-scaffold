import { usePage } from "@inertiajs/react";
import { Award, HandshakeIcon, ShieldCheck } from "lucide-react";
import SiteLayout from "@/Layouts/SiteLayout";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        title: "من نحن",
        intro: "منصة عقارية مصرية بتجمع العقارات والكمبوندات والمطوّرين في مكان واحد — بأسعار حقيقية، إعلانات مراجعة، ومستشار بيمشي معاك خطوة بخطوة من أول سؤال لحد استلام المفتاح.",
        stats: [
            ["500+", "مشروع منفّذ للفريق"],
            ["9+", "سنوات خبرة"],
            ["2", "سوق: مصر والخليج"],
        ],
        values: [
            ["الشفافية", "الأسعار والتفاصيل زي ما هي — من غير تجميل.", ShieldCheck],
            ["الجدية", "بنراجع كل إعلان قبل ما يوصلك.", Award],
            ["المرافقة", "مستشارك معاك لحد ما توقّع.", HandshakeIcon],
        ],
        note: "النص ده placeholder — بيتعدل بالكامل من الداشبورد بعد تركيب موديول Pages في المرحلة 3.",
    },
    en: {
        title: "About us",
        intro: "An Egyptian real-estate platform bringing properties, compounds, and developers into one place — real prices, reviewed listings, and an advisor who walks with you from first question to key handover.",
        stats: [
            ["500+", "Projects delivered by the team"],
            ["9+", "Years of experience"],
            ["2", "Markets: Egypt and the Gulf"],
        ],
        values: [
            ["Transparency", "Prices and details as they are — no varnish.", ShieldCheck],
            ["Rigor", "Every listing is reviewed before it reaches you.", Award],
            ["Partnership", "Your advisor stays with you until you sign.", HandshakeIcon],
        ],
        note: "This copy is a placeholder — fully editable from the dashboard once the Pages module lands in Phase 3.",
    },
};

export default function About() {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    return (
        <SiteLayout>
            <section className="border-b border-gray-100 bg-surface">
                <div className="mx-auto max-w-7xl px-4 py-16 text-center">
                    <h1 className="text-3xl text-secondary md:text-4xl">{t.title}</h1>
                    <p className="mx-auto mt-4 max-w-2xl text-base leading-loose text-muted">{t.intro}</p>
                </div>
            </section>

            <section className="bg-bg">
                <div className="mx-auto max-w-7xl px-4 py-16">
                    <div className="grid gap-4 sm:grid-cols-3">
                        {t.stats.map(([value, label]) => (
                            <div key={label} className="rounded-2xl border border-gray-100 bg-bg p-8 text-center shadow-sm">
                                <div className="text-4xl font-extrabold text-primary">{value}</div>
                                <div className="mt-2 text-sm font-bold text-muted">{label}</div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-14 grid gap-5 md:grid-cols-3">
                        {t.values.map(([title, desc, Icon]) => (
                            <div key={title as string} className="rounded-2xl border border-gray-100 bg-bg p-6">
                                <span className="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    {/* @ts-expect-error lucide component from tuple */}
                                    <Icon size={22} />
                                </span>
                                <h3 className="mt-4 text-base font-extrabold text-secondary">{title as string}</h3>
                                <p className="mt-2 text-sm leading-relaxed text-muted">{desc as string}</p>
                            </div>
                        ))}
                    </div>

                    <p className="mt-12 rounded-xl bg-surface p-4 text-center text-xs text-muted">{t.note}</p>
                </div>
            </section>
        </SiteLayout>
    );
}
