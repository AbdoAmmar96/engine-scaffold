import { useForm, usePage } from "@inertiajs/react";
import AuthShell from "@/Components/site/AuthShell";
import FormField, { inputClass } from "@/Components/site/FormField";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        title: "كلمة مرور جديدة",
        desc: "اختار كلمة مرور جديدة لحسابك — ٨ حروف على الأقل.",
        email: "الإيميل",
        password: "كلمة المرور الجديدة",
        confirm: "تأكيد كلمة المرور",
        submit: "احفظ وادخل",
        sending: "جارٍ الحفظ…",
        note: "اللينك ده بيشتغل مرة واحدة بس.",
    },
    en: {
        title: "Choose a new password",
        desc: "Pick a new password for your account — at least 8 characters.",
        email: "Email",
        password: "New password",
        confirm: "Confirm password",
        submit: "Save and sign in",
        sending: "Saving…",
        note: "This link works only once.",
    },
};

export default function ResetPassword({ token, email }: { token: string; email: string }) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const { data, setData, post, processing, errors } = useForm({
        token,
        email,
        password: "",
        password_confirmation: "",
    });

    return (
        <AuthShell title={t.title} desc={t.desc} footer={t.note}>
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    post(`/${locale}/reset-password`);
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
