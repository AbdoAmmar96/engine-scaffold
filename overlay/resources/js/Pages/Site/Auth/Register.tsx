import { Link, useForm, usePage } from "@inertiajs/react";
import AuthShell from "@/Components/site/AuthShell";
import FormField, { inputClass } from "@/Components/site/FormField";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        title: "حساب جديد",
        desc: "احفظ العقارات التي أعجبتك وتابع طلباتك من مكان واحد.",
        name: "الاسم",
        email: "البريد الإلكتروني",
        phone: "رقم الهاتف",
        phoneHint: "هذا الرقم الذي سنتواصل به معك",
        password: "كلمة المرور",
        passwordHint: "8 حروف على الأقل",
        confirm: "تأكيد كلمة المرور",
        submit: "أنشئ الحساب",
        sending: "جارٍ الإنشاء…",
        have: "لديك حساب بالفعل؟",
        login: "سجّل دخول",
    },
    en: {
        title: "Create an account",
        desc: "Save the properties you like and follow your requests in one place.",
        name: "Name",
        email: "Email",
        phone: "Mobile",
        phoneHint: "This is how we'll reach you",
        password: "Password",
        passwordHint: "At least 8 characters",
        confirm: "Confirm password",
        submit: "Create account",
        sending: "Creating…",
        have: "Already have an account?",
        login: "Sign in",
    },
};

export default function Register() {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const { data, setData, post, processing, errors } = useForm({
        name: "",
        email: "",
        phone: "",
        password: "",
        password_confirmation: "",
    });

    return (
        <AuthShell
            title={t.title}
            desc={t.desc}
            footer={
                <>
                    {t.have}{" "}
                    <Link href={`/${locale}/login`} className="font-extrabold text-primary hover:underline">
                        {t.login}
                    </Link>
                </>
            }
        >
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    post(`/${locale}/register`);
                }}
                className="flex flex-col gap-4"
            >
                <FormField label={t.name} error={errors.name}>
                    <input
                        value={data.name}
                        onChange={(e) => setData("name", e.target.value)}
                        autoComplete="name"
                        className={inputClass}
                    />
                </FormField>

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

                <FormField label={t.phone} error={errors.phone} hint={t.phoneHint}>
                    <input
                        type="tel"
                        dir="ltr"
                        autoComplete="tel"
                        value={data.phone}
                        onChange={(e) => setData("phone", e.target.value)}
                        className={inputClass}
                    />
                </FormField>

                <FormField label={t.password} error={errors.password} hint={t.passwordHint}>
                    <input
                        type="password"
                        dir="ltr"
                        autoComplete="new-password"
                        value={data.password}
                        onChange={(e) => setData("password", e.target.value)}
                        className={inputClass}
                    />
                </FormField>

                <FormField label={t.confirm}>
                    <input
                        type="password"
                        dir="ltr"
                        autoComplete="new-password"
                        value={data.password_confirmation}
                        onChange={(e) => setData("password_confirmation", e.target.value)}
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
