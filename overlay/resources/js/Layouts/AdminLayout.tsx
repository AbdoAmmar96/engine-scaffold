import { Link, usePage } from "@inertiajs/react";
import {
    Building2,
    Home,
    Image as ImageIcon,
    LayoutDashboard,
    LogOut,
    Palette,
    Settings,
    Share2,
    Users,
} from "lucide-react";
import type { ReactNode } from "react";
import { FlashBanner } from "@/Components/admin/ui";
import type { SharedProps } from "@/lib/types";

const settingsNav = [
    { href: "/admin/settings/general", label: "عام", icon: Settings },
    { href: "/admin/settings/theme", label: "الهوية والألوان", icon: Palette },
    { href: "/admin/settings/branding", label: "اللوجو والميديا", icon: ImageIcon },
    { href: "/admin/settings/contact", label: "بيانات التواصل", icon: Users },
    { href: "/admin/settings/social", label: "السوشيال ميديا", icon: Share2 },
];

// موديولات الدومين — بتتفعّل تباعًا مع المراحل الجاية
const comingSoon = ["العقارات", "الكمبوندات", "المطوّرون", "المناطق", "الليدز", "المدونة"];

export default function AdminLayout({ title, children }: { title: string; children: ReactNode }) {
    const { auth } = usePage<SharedProps>().props;
    const path = typeof window !== "undefined" ? window.location.pathname : "";

    const item = (href: string, label: string, Icon: typeof Home, active: boolean) => (
        <Link
            key={href}
            href={href}
            className={`flex items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-bold transition ${
                active ? "bg-primary text-primary-fg" : "text-gray-300 hover:bg-white/10 hover:text-white"
            }`}
        >
            <Icon size={17} />
            {label}
        </Link>
    );

    return (
        <div dir="rtl" className="flex min-h-screen bg-gray-100 font-sans text-gray-900">
            {/* ------------------------------ Sidebar ------------------------------ */}
            <aside className="fixed inset-y-0 right-0 z-40 flex w-64 flex-col bg-bg-dark p-4">
                <div className="mb-8 flex items-center gap-2 px-2 pt-2">
                    <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary font-extrabold text-primary-fg">
                        BP
                    </span>
                    <span className="text-sm font-extrabold text-white">إنجن شريك الأعمال</span>
                </div>

                <nav className="flex flex-col gap-1">
                    {item("/admin", "لوحة التحكم", LayoutDashboard, path === "/admin")}

                    <div className="mt-4 mb-1 px-4 text-[11px] font-extrabold tracking-wide text-gray-500">الإعدادات</div>
                    {settingsNav.map((s) => item(s.href, s.label, s.icon, path.startsWith(s.href)))}

                    <div className="mt-4 mb-1 px-4 text-[11px] font-extrabold tracking-wide text-gray-500">الموديولات</div>
                    {comingSoon.map((label) => (
                        <span
                            key={label}
                            className="flex cursor-not-allowed items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-bold text-gray-600"
                        >
                            <Building2 size={17} />
                            {label}
                            <span className="ms-auto rounded-full bg-white/10 px-2 py-0.5 text-[10px]">قريبًا</span>
                        </span>
                    ))}
                </nav>

                <div className="mt-auto border-t border-white/10 pt-4">
                    <div className="px-2 text-xs text-gray-400">{auth.user?.name}</div>
                    <Link
                        href="/admin/logout"
                        method="post"
                        as="button"
                        className="mt-2 flex w-full items-center gap-3 rounded-xl px-4 py-2.5 text-sm font-bold text-gray-300 hover:bg-white/10 hover:text-white"
                    >
                        <LogOut size={17} />
                        تسجيل الخروج
                    </Link>
                </div>
            </aside>

            {/* ------------------------------ Content ------------------------------ */}
            <div className="flex-1 pe-64">
                <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-gray-200 bg-white/90 px-8 backdrop-blur">
                    <h1 className="text-lg font-extrabold">{title}</h1>
                    <a href="/ar" target="_blank" className="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-secondary">
                        <Home size={16} />
                        عرض الموقع
                    </a>
                </header>

                <main className="p-8">{children}</main>
            </div>

            <FlashBanner />
        </div>
    );
}
