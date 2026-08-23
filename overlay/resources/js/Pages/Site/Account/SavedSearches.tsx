import { Link, router, useForm, usePage } from "@inertiajs/react";
import { Bell, BellOff, Pencil, Search, Trash2 } from "lucide-react";
import { useState } from "react";
import AccountLayout from "@/Layouts/AccountLayout";
import { inputClass } from "@/Components/site/FormField";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        title: "البحث المحفوظ",
        empty: "لا يوجد بحث محفوظ بعد",
        emptyText: "فلتر العقارات كما تريد، ثم اضغط «احفظ هذا البحث» — وسنرسل إليك فور توفّر ما يطابقه.",
        browse: "ابدأ الفلترة",
        matches: (n: number) => (n === 1 ? "وحدة مطابقة حاليًا" : `${n} وحدة مطابقة حاليًا`),
        open: "افتح النتائج",
        alertsOn: "التنبيه مفعّل",
        alertsOff: "التنبيه متوقّف",
        rename: "غيّر الاسم",
        save: "احفظ",
        cancel: "إلغاء",
        remove: "حذف",
        confirm: "هل تريد حذف هذا البحث؟",
        last: "آخر تنبيه:",
    },
    en: {
        title: "Saved searches",
        empty: "No saved searches yet",
        emptyText: "Filter the listings the way you want, then hit “Save this search” — we'll email you when something matches.",
        browse: "Go and filter",
        matches: (n: number) => (n === 1 ? "1 match right now" : `${n} matches right now`),
        open: "Open results",
        alertsOn: "Alerts on",
        alertsOff: "Alerts off",
        rename: "Rename",
        save: "Save",
        cancel: "Cancel",
        remove: "Delete",
        confirm: "Delete this search?",
        last: "Last alert:",
    },
};

interface Saved {
    id: number;
    name: string;
    summary: string[];
    url: string;
    alerts: boolean;
    matches: number;
    lastAlert: string | null;
}

/** «البحث المحفوظ» — العميل بيتابع سوق معيّن من غير ما يفضل يفلتر كل مرة */
export default function SavedSearches({ searches }: { searches: Saved[] }) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const [editing, setEditing] = useState<number | null>(null);
    const { data, setData, put, processing } = useForm({ name: "", alerts: true });

    const startEdit = (s: Saved) => {
        setData({ name: s.name, alerts: s.alerts });
        setEditing(s.id);
    };

    const toggleAlerts = (s: Saved) =>
        router.put(
            `/${locale}/account/saved-searches/${s.id}`,
            { name: s.name, alerts: !s.alerts },
            { preserveScroll: true },
        );

    return (
        <AccountLayout title={t.title}>
            {searches.length === 0 ? (
                <div className="flex flex-col items-center rounded-3xl border border-gray-100 bg-bg px-6 py-16 text-center">
                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <Search size={26} />
                    </span>
                    <h3 className="mt-4 text-lg font-extrabold text-secondary">{t.empty}</h3>
                    <p className="mt-2 max-w-md text-sm leading-7 text-muted">{t.emptyText}</p>
                    <Link
                        href={`/${locale}/properties`}
                        className="mt-6 rounded-brand bg-primary px-6 py-2.5 text-[13px] font-extrabold text-primary-fg transition hover:bg-primary-hover"
                    >
                        {t.browse}
                    </Link>
                </div>
            ) : (
                <ul className="flex flex-col gap-4">
                    {searches.map((s) => (
                        <li key={s.id} className="flex flex-col gap-3 rounded-3xl border border-gray-100 bg-bg p-5">
                            {editing === s.id ? (
                                <form
                                    onSubmit={(e) => {
                                        e.preventDefault();
                                        put(`/${locale}/account/saved-searches/${s.id}`, {
                                            preserveScroll: true,
                                            onSuccess: () => setEditing(null),
                                        });
                                    }}
                                    className="flex flex-wrap items-center gap-2"
                                >
                                    <input
                                        value={data.name}
                                        onChange={(e) => setData("name", e.target.value)}
                                        className={`${inputClass} max-w-sm`}
                                        autoFocus
                                    />
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="rounded-brand bg-primary px-4 py-2.5 text-[12px] font-extrabold text-primary-fg disabled:opacity-60"
                                    >
                                        {t.save}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setEditing(null)}
                                        className="text-[12px] font-extrabold text-muted hover:text-danger"
                                    >
                                        {t.cancel}
                                    </button>
                                </form>
                            ) : (
                                <h3 className="text-[15px] font-extrabold text-secondary">{s.name}</h3>
                            )}

                            {s.summary.length > 0 && (
                                <ul className="flex flex-wrap gap-1.5">
                                    {s.summary.map((chip) => (
                                        <li
                                            key={chip}
                                            dir="auto"
                                            className="rounded-full bg-surface px-2.5 py-1 text-[11px] font-bold text-muted"
                                        >
                                            {chip}
                                        </li>
                                    ))}
                                </ul>
                            )}

                            <p className="text-[12px] font-bold text-muted">
                                <span dir="auto" className="text-secondary">{s.matches}</span>{" "}
                                {t.matches(s.matches).replace(String(s.matches), "").trim()}
                                {s.lastAlert && <span className="ms-3">{t.last} {s.lastAlert}</span>}
                            </p>

                            <div className="flex flex-wrap items-center gap-2">
                                <Link
                                    href={s.url}
                                    className="rounded-brand bg-primary px-4 py-2 text-[12px] font-extrabold text-primary-fg transition hover:bg-primary-hover"
                                >
                                    {t.open}
                                </Link>

                                <button
                                    type="button"
                                    onClick={() => toggleAlerts(s)}
                                    className={`flex items-center gap-1.5 rounded-brand border px-3 py-2 text-[12px] font-extrabold transition ${
                                        s.alerts
                                            ? "border-success/40 bg-success/5 text-success"
                                            : "border-gray-200 text-muted hover:border-primary hover:text-primary"
                                    }`}
                                >
                                    {s.alerts ? <Bell size={13} /> : <BellOff size={13} />}
                                    {s.alerts ? t.alertsOn : t.alertsOff}
                                </button>

                                <button
                                    type="button"
                                    onClick={() => startEdit(s)}
                                    className="flex items-center gap-1.5 rounded-brand border border-gray-200 px-3 py-2 text-[12px] font-extrabold text-secondary transition hover:border-primary hover:text-primary"
                                >
                                    <Pencil size={13} />
                                    {t.rename}
                                </button>

                                <button
                                    type="button"
                                    onClick={() => {
                                        if (confirm(t.confirm)) {
                                            router.delete(`/${locale}/account/saved-searches/${s.id}`, { preserveScroll: true });
                                        }
                                    }}
                                    className="flex items-center gap-1.5 rounded-brand border border-gray-200 px-3 py-2 text-[12px] font-extrabold text-muted transition hover:border-danger hover:text-danger"
                                >
                                    <Trash2 size={13} />
                                    {t.remove}
                                </button>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </AccountLayout>
    );
}
