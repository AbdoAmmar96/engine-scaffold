import { Link, router, usePage } from "@inertiajs/react";
import { AlertCircle, Building2, CheckCircle2, Heart, Inbox, LogOut, Search, UserRound } from "lucide-react";
import type { ReactNode } from "react";
import SiteLayout from "@/Layouts/SiteLayout";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: { overview: "نظرة عامة", favorites: "المحفوظة", requests: "طلباتي", listings: "وحداتي", searches: "البحث المحفوظ", logout: "خروج", hello: "أهلًا" },
    en: { overview: "Overview", favorites: "Saved", requests: "My requests", listings: "My listings", searches: "Saved searches", logout: "Sign out", hello: "Hi" },
};

/**
 * مساحة العميل — بتلبس تصميم الموقع مش شكل لوحة التحكم،
 * عشان الزائر ميحسّش إنه اتنقل لنظام تاني.
 */
export default function AccountLayout({ title, children }: { title: string; children: ReactNode }) {
    const { locale, auth, flash } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;
    const path = typeof window !== "undefined" ? window.location.pathname : "";

    // «وحداتي» بتظهر للمعلن والوسيط والشركة بس — نفس الصلاحية اللي على الراوت
    const lists = auth.user?.can?.includes("manage listings");

    const tabs = [
        { href: `/${locale}/account`, label: t.overview, icon: UserRound, exact: true },
        ...(lists
            ? [{ href: `/${locale}/account/my-properties`, label: t.listings, icon: Building2, exact: false }]
            : []),
        { href: `/${locale}/account/favorites`, label: t.favorites, icon: Heart, exact: false },
        { href: `/${locale}/account/saved-searches`, label: t.searches, icon: Search, exact: false },
        { href: `/${locale}/account/requests`, label: t.requests, icon: Inbox, exact: false },
    ];

    return (
        <SiteLayout>
            <section className="bg-bg-dark px-4 py-10">
                <div className="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4">
                    <div>
                        <span className="text-[12px] font-bold text-white/50">{t.hello}</span>
                        <h1 className="mt-1 text-2xl font-black text-white">{auth.user?.name}</h1>
                    </div>

                    <button
                        onClick={() => router.post(`/${locale}/logout`)}
                        className="flex items-center gap-2 rounded-brand border border-white/20 px-4 py-2.5 text-[13px] font-extrabold text-white/80 transition hover:border-primary hover:text-primary"
                    >
                        <LogOut size={15} />
                        {t.logout}
                    </button>
                </div>
            </section>

            <section className="border-b border-gray-100 bg-bg px-4">
                <nav className="mx-auto flex max-w-5xl gap-1 overflow-x-auto">
                    {tabs.map(({ href, label, icon: Icon, exact }) => {
                        const active = exact ? path === href : path.startsWith(href);

                        return (
                            <Link
                                key={href}
                                href={href}
                                className={`flex shrink-0 items-center gap-2 border-b-2 px-4 py-4 text-[13px] font-extrabold transition ${
                                    active
                                        ? "border-primary text-secondary"
                                        : "border-transparent text-muted hover:text-primary"
                                }`}
                            >
                                <Icon size={15} />
                                {label}
                            </Link>
                        );
                    })}
                </nav>
            </section>

            <section className="bg-surface px-4 py-10">
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    {flash.error && (
                        <div className="flex items-center gap-2 rounded-2xl border border-danger/30 bg-danger/10 px-5 py-3.5 text-[13px] font-extrabold text-danger">
                            <AlertCircle size={16} />
                            {flash.error}
                        </div>
                    )}

                    {flash.success && (
                        <div className="flex items-center gap-2 rounded-2xl border border-success/30 bg-success/10 px-5 py-3.5 text-[13px] font-extrabold text-success">
                            <CheckCircle2 size={16} />
                            {flash.success}
                        </div>
                    )}

                    <h2 className="text-xl font-extrabold text-secondary">{title}</h2>

                    {children}
                </div>
            </section>
        </SiteLayout>
    );
}
