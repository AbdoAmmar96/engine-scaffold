import { usePage } from "@inertiajs/react";
import { Mail, MapPin, Phone } from "lucide-react";
import { useState } from "react";
import SiteLayout from "@/Layouts/SiteLayout";
import type { SharedProps } from "@/lib/types";

/**
 * Contact v0 — الفورم بيبني رسالة واتساب جاهزة وبيفتحها (شغال فعليًا لو الرقم مضبوط
 * في الإعدادات). في المرحلة 5 بيتوصل بموديول Leads فيتسجل في الداشبورد قبل الإرسال.
 */
export default function Contact() {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const contact = settings.contact ?? {};

    const [form, setForm] = useState({ name: "", phone: "", intent: "", message: "" });
    const [error, setError] = useState("");

    const intents = ar
        ? ["شراء عقار", "استئجار عقار", "بيع عقاري", "عرض عقاري للإيجار"]
        : ["Buy a property", "Rent a property", "Sell my property", "List my property for rent"];

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!form.name.trim() || !form.phone.trim()) {
            setError(ar ? "اكتب اسمك ورقم موبايلك الأول" : "Enter your name and phone first");
            return;
        }
        setError("");

        const lines = ar
            ? [`الاسم: ${form.name}`, `الموبايل: ${form.phone}`, form.intent && `أرغب في: ${form.intent}`, form.message && `تفاصيل: ${form.message}`]
            : [`Name: ${form.name}`, `Phone: ${form.phone}`, form.intent && `I want to: ${form.intent}`, form.message && `Details: ${form.message}`];

        const text = encodeURIComponent(lines.filter(Boolean).join("\n"));

        if (contact.whatsapp) {
            window.open(`https://wa.me/${contact.whatsapp}?text=${text}`, "_blank");
        } else {
            setError(ar ? "رقم الواتساب لسه متضبطش من الداشبورد (الإعدادات → بيانات التواصل)" : "WhatsApp number not set yet (Dashboard → Contact settings)");
        }
    };

    const input =
        "w-full rounded-lg border border-gray-200 bg-bg px-3.5 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/25";

    return (
        <SiteLayout>
            <section className="border-b border-gray-100 bg-surface">
                <div className="mx-auto max-w-7xl px-4 py-12">
                    <h1 className="text-3xl text-secondary md:text-4xl">{ar ? "اتصل بنا" : "Contact us"}</h1>
                    <p className="mt-2 text-sm text-muted">
                        {ar ? "سيب بياناتك وهيتواصل معاك مستشار مباشرة." : "Leave your details and an advisor will reach out directly."}
                    </p>
                </div>
            </section>

            <section className="bg-bg">
                <div className="mx-auto grid max-w-7xl gap-10 px-4 py-14 lg:grid-cols-3">
                    <div className="flex flex-col gap-4">
                        {contact.phone && (
                            <div className="flex items-center gap-3 rounded-2xl border border-gray-100 p-4">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><Phone size={18} /></span>
                                <span className="text-sm font-bold text-secondary" dir="ltr">{contact.phone}</span>
                            </div>
                        )}
                        {contact.email && (
                            <div className="flex items-center gap-3 rounded-2xl border border-gray-100 p-4">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><Mail size={18} /></span>
                                <span className="text-sm font-bold text-secondary" dir="ltr">{contact.email}</span>
                            </div>
                        )}
                        {contact.address && (
                            <div className="flex items-center gap-3 rounded-2xl border border-gray-100 p-4">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary"><MapPin size={18} /></span>
                                <span className="text-sm font-bold text-secondary">{contact.address}</span>
                            </div>
                        )}
                        {!contact.phone && !contact.email && !contact.address && (
                            <p className="rounded-2xl bg-surface p-4 text-xs leading-relaxed text-muted">
                                {ar
                                    ? "بيانات التواصل بتظهر هنا تلقائيًا أول ما تتسجل من الداشبورد → الإعدادات → بيانات التواصل."
                                    : "Contact details appear here automatically once set from Dashboard → Contact settings."}
                            </p>
                        )}
                    </div>

                    <form onSubmit={submit} className="rounded-2xl border border-gray-100 bg-bg p-6 shadow-sm lg:col-span-2">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <input className={input} placeholder={ar ? "الاسم بالكامل" : "Full name"} value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
                            <input className={input} dir="ltr" placeholder={ar ? "رقم الموبايل" : "Phone number"} value={form.phone} onChange={(e) => setForm({ ...form, phone: e.target.value })} />
                            <select className={`${input} sm:col-span-2`} value={form.intent} onChange={(e) => setForm({ ...form, intent: e.target.value })}>
                                <option value="">{ar ? "أرغب في…" : "I want to…"}</option>
                                {intents.map((i) => (
                                    <option key={i} value={i}>{i}</option>
                                ))}
                            </select>
                            <textarea className={`${input} sm:col-span-2`} rows={4} placeholder={ar ? "ملاحظات / طلبات خاصة" : "Notes / special requests"} value={form.message} onChange={(e) => setForm({ ...form, message: e.target.value })} />
                        </div>

                        {error && <p className="mt-3 text-xs font-bold text-danger">{error}</p>}

                        <button type="submit" className="mt-5 w-full rounded-brand bg-primary py-3.5 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover sm:w-auto sm:px-10">
                            {ar ? "إرسال عبر واتساب" : "Send via WhatsApp"}
                        </button>
                    </form>
                </div>
            </section>
        </SiteLayout>
    );
}
