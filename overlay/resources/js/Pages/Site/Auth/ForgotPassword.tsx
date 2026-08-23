import { Link, useForm, usePage } from "@inertiajs/react";
import { CheckCircle2 } from "lucide-react";
import AuthShell from "@/Components/site/AuthShell";
import FormField, { inputClass } from "@/Components/site/FormField";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        title: "استعادة كلمة المرور",
        desc: "اكتب بريد حسابك وسنرسل إليك رابطًا لاختيار كلمة مرور جديدة.",
        email: "البريد الإلكتروني",
        submit: "أرسل الرابط",
        sending: "جارٍ الإرسال…",
        back: "تذكّرتها؟",
        login: "تسجيل الدخول",
    },
    en: {
        title: "Reset your password",
        desc: "Enter your account email and we'll send you a link to choose a new password.",
        email: "Email",
        submit: "Send me the link",
        sending: "Sending…",
        back: "Remembered it?",
        login: "Sign in",
    },
};

export default function ForgotPassword() {
    const { locale, flash } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const { data, setData, post, processing, errors } = useForm({ email: "" });

    return (
        <AuthShell
            title={t.title}
            desc={t.desc}
            footer={
                <>
                    {t.back}{" "}
                    <Link href={`/${locale}/login`} className="font-extrabold text-primary hover:underline">
                        {t.login}
                    </Link>
                </>
            }
        >
            {flash.success && (
                <p className="mb-4 flex items-center gap-2 rounded-2xl border border-success/30 bg-success/10 px-4 py-3 text-[13px] font-extrabold text-success">
                    <CheckCircle2 size={16} className="shrink-0" />
                    {flash.success}
                </p>
            )}

            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    post(`/${locale}/forgot-password`);
                }}
                className="flex flex-col gap-4"
            >
                <FormField label={t.email} error={errors.email}>
                    <input
                        type="email"
                        dir="ltr"
                        autoComplete="email"
                        value={data.email}
                        onChange={(e) => setData("email", e.target.value)}
                        className={inputClass}
                    />
                </FormField>

                <button
                    type="submit"
                    disabled={processing}
                    className="mt-1 rounded-brand bg-primary py-3 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover disabled:opacity-60"
                >
                    {processing ? t.sending : t.submit}
                </button>
            </form>
        </AuthShell>
    );
}
