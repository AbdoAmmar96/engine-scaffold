import { router, usePage } from "@inertiajs/react";
import { CheckCircle2, Send } from "lucide-react";
import { useState } from "react";
import FormField, { inputClass } from "@/Components/site/FormField";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        name: "الاسم",
        phone: "الموبايل",
        email: "الإيميل (اختياري)",
        message: "رسالتك",
        placeholder: "مثال: عايز أعاين الوحدة نهاية الأسبوع.",
        submit: "ابعت الطلب",
        sending: "جارٍ الإرسال…",
        done: "وصلنا طلبك ✅ — هنتواصل معاك في أقرب وقت.",
        another: "ابعت طلب تاني",
    },
    en: {
        name: "Name",
        phone: "Mobile",
        email: "Email (optional)",
        message: "Your message",
        placeholder: "e.g. I'd like to view the unit this weekend.",
        submit: "Send request",
        sending: "Sending…",
        done: "Request received ✅ — we'll get back to you shortly.",
        another: "Send another request",
    },
};

/**
 * فورم الطلب على صفحة الوحدة/المشروع. بيبعت property_id أو compound_id
 * عشان السيرفر يوجّه الطلب لصاحب الوحدة — من غير كده كل الطلبات بتتكوّم عند الأدمن.
 */
export default function LeadForm({
    propertyId,
    compoundId,
    source,
    subject,
}: {
    propertyId?: number;
    compoundId?: number;
    source: "property" | "compound";
    /** اسم الوحدة/المشروع — بيتحط في الرسالة الافتراضية */
    subject: string;
}) {
    const { locale, auth } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const [sent, setSent] = useState(false);
    const [sending, setSending] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [form, setForm] = useState({
        name: auth.user?.name ?? "",
        phone: "",
        email: auth.user?.email ?? "",
        message: "",
        website: "",
    });

    const set = (k: keyof typeof form) => (e: { target: { value: string } }) =>
        setForm((f) => ({ ...f, [k]: e.target.value }));

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        setSending(true);

        router.post(
            `/${locale}/leads`,
            {
                ...form,
                area: subject,
                source,
                property_id: propertyId ?? null,
                compound_id: compoundId ?? null,
            },
            {
                preserveScroll: true,
                onFinish: () => setSending(false),
                onError: (e) => setErrors(e as Record<string, string>),
                onSuccess: () => {
                    setErrors({});
                    setSent(true);
                },
            },
        );
    };

    if (sent) {
        return (
            <div className="flex flex-col items-start gap-4 rounded-2xl border border-success/30 bg-success/10 p-6">
                <p className="flex items-center gap-2 text-sm font-extrabold text-success">
                    <CheckCircle2 size={18} />
                    {t.done}
                </p>
                <button
                    type="button"
                    onClick={() => {
                        setForm((f) => ({ ...f, message: "" }));
                        setSent(false);
                    }}
                    className="text-[13px] font-extrabold text-secondary underline transition hover:text-primary"
                >
                    {t.another}
                </button>
            </div>
        );
    }

    return (
        <form onSubmit={submit} className="rounded-2xl border border-gray-100 bg-surface p-6">
            <div className="grid gap-4 sm:grid-cols-2">
                <FormField label={t.name} error={errors.name}>
                    <input value={form.name} onChange={set("name")} autoComplete="name" className={inputClass} />
                </FormField>

                <FormField label={t.phone} error={errors.phone}>
                    <input
                        type="tel"
                        dir="ltr"
                        value={form.phone}
                        onChange={set("phone")}
                        autoComplete="tel"
                        className={inputClass}
                    />
                </FormField>

                <div className="sm:col-span-2">
                    <FormField label={t.email} error={errors.email}>
                        <input
                            type="email"
                            dir="ltr"
                            value={form.email}
                            onChange={set("email")}
                            autoComplete="email"
                            className={inputClass}
                        />
                    </FormField>
                </div>

                <div className="sm:col-span-2">
                    <FormField label={t.message} error={errors.message}>
                        <textarea
                            rows={3}
                            value={form.message}
                            onChange={set("message")}
                            placeholder={t.placeholder}
                            className={inputClass}
                        />
                    </FormField>
                </div>
            </div>

            {/* مصيدة بوتس — مخفية عن البني آدمين */}
            <input
                type="text"
                name="website"
                value={form.website}
                onChange={set("website")}
                tabIndex={-1}
                autoComplete="off"
                aria-hidden="true"
                className="hidden"
            />

            <button
                type="submit"
                disabled={sending}
                className="mt-5 flex items-center gap-2 rounded-brand bg-primary px-6 py-3 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover disabled:opacity-60"
            >
                <Send size={15} />
                {sending ? t.sending : t.submit}
            </button>
        </form>
    );
}
