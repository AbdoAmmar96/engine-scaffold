import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, Inbox } from "lucide-react";
import AccountLayout from "@/Layouts/AccountLayout";
import type { AccountRequest, SharedProps } from "@/lib/types";

const toneStyles: Record<string, string> = {
    primary: "bg-primary/10 text-primary",
    success: "bg-success/10 text-success",
    warn: "bg-amber-100 text-amber-700",
    muted: "bg-gray-100 text-gray-500",
    danger: "bg-danger/10 text-danger",
};

export default function Requests({ requests }: { requests: AccountRequest[] }) {
    const { locale } = usePage<SharedProps>().props;
    const ar = locale === "ar";

    return (
        <AccountLayout title={ar ? "طلباتي" : "My requests"}>
            {requests.length > 0 ? (
                <div className="flex flex-col gap-3">
                    {requests.map((r) => (
                        <article key={r.id} className="rounded-2xl border border-gray-100 bg-bg p-5">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div className="min-w-0">
                                    {r.link ? (
                                        <Link
                                            href={r.link}
                                            className="flex items-center gap-1.5 text-[15px] font-extrabold text-secondary transition hover:text-primary"
                                        >
                                            {r.subject}
                                            <ArrowLeft size={14} className="text-primary ltr:rotate-180" />
                                        </Link>
                                    ) : (
                                        <span className="text-[15px] font-extrabold text-secondary">{r.subject}</span>
                                    )}
                                    <span className="mt-1 block text-[11px] font-bold text-muted" dir="auto">
                                        {r.date}
                                    </span>
                                </div>

                                <span
                                    className={`shrink-0 rounded-full px-3 py-1.5 text-[11px] font-extrabold ${
                                        toneStyles[r.status.tone] ?? toneStyles.muted
                                    }`}
                                >
                                    {r.status.label}
                                </span>
                            </div>

                            {r.message && (
                                <p className="mt-3 border-t border-gray-100 pt-3 text-[13px] leading-[1.9] text-muted">
                                    {r.message}
                                </p>
                            )}
                        </article>
                    ))}
                </div>
            ) : (
                <div className="flex flex-col items-center rounded-2xl border border-gray-100 bg-bg px-6 py-16 text-center">
                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <Inbox size={24} />
                    </span>
                    <p className="mt-4 text-sm text-muted">
                        {ar
                            ? "لم ترسل أي طلب بعد — اطلب معاينة من صفحة أي وحدة وستجده هنا."
                            : "You haven't sent any request yet — ask for a viewing from any unit page."}
                    </p>
                    <Link
                        href={`/${locale}/properties`}
                        className="mt-6 rounded-brand bg-primary px-6 py-3 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover"
                    >
                        {ar ? "تصفّح العقارات" : "Browse properties"}
                    </Link>
                </div>
            )}
        </AccountLayout>
    );
}
