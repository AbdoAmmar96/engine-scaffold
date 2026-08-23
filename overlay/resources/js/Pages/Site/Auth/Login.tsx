import { Link, useForm, usePage } from "@inertiajs/react";
import AuthShell from "@/Components/site/AuthShell";
import FormField, { inputClass } from "@/Components/site/FormField";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        title: "تسجيل الدخول",
        desc: "ادخل إلى حسابك لعرض العقارات التي حفظتها ومتابعة طلباتك.",
        email: "البريد الإلكتروني",
        password: "كلمة المرور",
        remember: "تذكّرني",
        submit: "دخول",
        sending: "جارٍ الدخول…",
        noAccount: "ليس لديك حساب بعد؟",
        register: "أنشئ حسابًا",
        forgot: "نسيت كلمة المرور؟",
    },
    en: {
        title: "Sign in",
        desc: "Sign in to see your saved properties and follow up on your requests.",
        email: "Email",
        password: "Password",
        remember: "Keep me signed in",
        submit: "Sign in",
        sending: "Signing in…",
        noAccount: "Don't have an account?",
        register: "Create one",
        forgot: "Forgot your password?",
    },
};

export default function Login() {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const { data, setData, post, processing, errors } = useForm({
        email: "",
        password: "",
        remember: false,
    });

    return (
        <AuthShell
            title={t.title}
            desc={t.desc}
            footer={
                <>
                    {t.noAccount}{" "}
                    <Link href={`/${locale}/register`} className="font-extrabold text-primary hover:underline">
                        {t.register}
                    </Link>
                </>
            }
        >
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    post(`/${locale}/login`);
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

                <FormField label={t.password} error={errors.password}>
                    <input
                        type="password"
                        dir="ltr"
                        autoComplete="current-password"
                        value={data.password}
                        onChange={(e) => setData("password", e.target.value)}
                        className={inputClass}
                    />
                </FormField>

                <div className="flex flex-wrap items-center justify-between gap-2">
                    <label className="flex items-center gap-2 text-[13px] font-bold text-muted">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={(e) => setData("remember", e.target.checked)}
                            className="h-4 w-4 accent-[var(--primary)]"
                        />
                        {t.remember}
                    </label>

                    <Link
                        href={`/${locale}/forgot-password`}
                        className="text-[13px] font-extrabold text-primary hover:underline"
                    >
                        {t.forgot}
                    </Link>
                </div>

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
