import { Link, useForm, usePage } from "@inertiajs/react";
import { Heart, Inbox, Loader2 } from "lucide-react";
import AccountLayout from "@/Layouts/AccountLayout";
import FormField, { inputClass } from "@/Components/site/FormField";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        title: "نظرة عامة",
        saved: "عقار محفوظ",
        listings: "وحدة معروضة",
        requests: "طلب مبعوت",
        open: "طلب لسه مفتوح",
        profile: "بياناتي",
        name: "الاسم",
        email: "الإيميل",
        phone: "الموبايل",
        password: "كلمة مرور جديدة",
        passwordHint: "سيبها فاضية لو مش عايز تغيّرها",
        confirm: "تأكيد كلمة المرور",
        save: "حفظ",
        saving: "جارٍ الحفظ…",
        browse: "تصفّح العقارات",
    },
    en: {
        title: "Overview",
        saved: "saved properties",
        listings: "listings",
        requests: "requests sent",
        open: "still open",
        profile: "My details",
        name: "Name",
        email: "Email",
        phone: "Mobile",
        password: "New password",
        passwordHint: "Leave empty to keep your current one",
        confirm: "Confirm password",
        save: "Save",
        saving: "Saving…",
        browse: "Browse properties",
    },
};

export default function AccountIndex({
    stats,
    profile,
}: {
    stats: { favorites: number; requests: number; open: number; listings: number | null };
    profile: { name: string; email: string; phone: string };
}) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const { data, setData, put, processing, errors } = useForm({
        ...profile,
        password: "",
        password_confirmation: "",
    });

    const stat = (value: number, label: string, href?: string) => {
        const body = (
            <>
                <span className="text-3xl font-black text-primary" dir="ltr">
                    {value}
                </span>
                <span className="mt-1 text-[13px] font-bold text-muted">{label}</span>
            </>
        );

        return href ? (
            <Link
                href={href}
                className="flex flex-col rounded-2xl border border-gray-100 bg-bg p-6 transition hover:border-primary/50 hover:shadow-sm"
            >
                {body}
            </Link>
        ) : (
            <div className="flex flex-col rounded-2xl border border-gray-100 bg-bg p-6">{body}</div>
        );
    };

    return (
        <AccountLayout title={t.title}>
            <div className="grid gap-4 sm:grid-cols-3">
                {stats.listings !== null &&
                    stat(stats.listings, t.listings, `/${locale}/account/my-properties`)}
                {stat(stats.favorites, t.saved, `/${locale}/account/favorites`)}
                {stat(stats.requests, t.requests, `/${locale}/account/requests`)}
                {stats.listings === null && stat(stats.open, t.open)}
            </div>

            {stats.listings === null && stats.favorites === 0 && stats.requests === 0 && (
                <div className="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-bg-dark p-6">
                    <p className="text-sm font-bold text-white/75">
                        {locale === "en"
                            ? "Nothing here yet — save a property and it shows up in your list."
                            : "لسه مفيش حاجة — احفظ أي عقار وهتلاقيه هنا."}
                    </p>
                    <Link
                        href={`/${locale}/properties`}
                        className="flex items-center gap-2 rounded-brand bg-primary px-5 py-2.5 text-[13px] font-extrabold text-primary-fg transition hover:opacity-90"
                    >
                        <Heart size={15} />
                        {t.browse}
                    </Link>
                </div>
            )}

            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    put(`/${locale}/account`);
                }}
                className="rounded-2xl border border-gray-100 bg-bg p-6"
            >
                <h3 className="mb-5 flex items-center gap-2 text-base font-extrabold text-secondary">
                    <Inbox size={17} className="text-primary" />
                    {t.profile}
                </h3>

                <div className="grid gap-4 sm:grid-cols-2">
                    <FormField label={t.name} error={errors.name}>
                        <input value={data.name} onChange={(e) => setData("name", e.target.value)} className={inputClass} />
                    </FormField>

                    <FormField label={t.email} error={errors.email}>
                        <input
                            type="email"
                            dir="ltr"
                            value={data.email}
                            onChange={(e) => setData("email", e.target.value)}
                            className={inputClass}
                        />
                    </FormField>

                    <FormField label={t.phone} error={errors.phone}>
                        <input
                            type="tel"
                            dir="ltr"
                            value={data.phone}
                            onChange={(e) => setData("phone", e.target.value)}
                            className={inputClass}
                        />
                    </FormField>

                    <div className="hidden sm:block" />

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
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="mt-6 flex items-center gap-2 rounded-brand bg-primary px-6 py-3 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover disabled:opacity-60"
                >
                    {processing && <Loader2 size={15} className="animate-spin" />}
                    {processing ? t.saving : t.save}
                </button>
            </form>
        </AccountLayout>
    );
}
