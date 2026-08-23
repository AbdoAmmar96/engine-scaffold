import { Link, usePage } from "@inertiajs/react";
import {
    BarChart3,
    Briefcase,
    Building2,
    FileText,
    Home,
    Image as ImageIcon,
    Images,
    Inbox,
    LayoutDashboard,
    Link2,
    ListTree,
    LogOut,
    MapPin,
    Megaphone,
    Newspaper,
    Palette,
    Phone,
    ScrollText,
    Search,
    Settings,
    Share2,
    UserCog,
} from "lucide-react";
import type { ReactNode } from "react";
import { FlashBanner } from "@/Components/admin/ui";
import type { SharedProps } from "@/lib/types";

const SETTINGS = "manage settings";
const CONTENT = "manage content";
const MEDIA = "manage media";
const CATALOG = "manage catalog";
const LISTINGS = "manage listings";
const PROJECTS = "manage projects";
const LEADS = "manage leads";
const FEATURE = "feature listings";
const USERS = "manage users";
const ROLES = "manage roles";
const REPORTS = "view reports";

/** اللينك بيظهر لو معاه أي صلاحية من دول — الأدمن والوسيط بيدخلوا نفس الشاشة */
type NavItem = { href: string; label: string; icon: typeof Home; perm: string[] };

const settingsNav: NavItem[] = [
    { href: "/admin/settings/general", label: "عام", icon: Settings, perm: [SETTINGS] },
    { href: "/admin/settings/theme", label: "الهوية والألوان", icon: Palette, perm: [SETTINGS] },
    { href: "/admin/settings/branding", label: "اللوجو والميديا", icon: ImageIcon, perm: [SETTINGS] },
    { href: "/admin/settings/about", label: "صفحة من نحن", icon: FileText, perm: [SETTINGS] },
    { href: "/admin/settings/contact", label: "بيانات التواصل", icon: Phone, perm: [SETTINGS] },
    { href: "/admin/settings/social", label: "السوشيال ميديا", icon: Share2, perm: [SETTINGS] },
    { href: "/admin/settings/seo", label: "السيو", icon: Search, perm: [SETTINGS] },
    { href: "/admin/settings/integrations", label: "التكاملات", icon: Link2, perm: [SETTINGS] },
];

// محتوى مشترك بين كل الصفحات
const contentNav: NavItem[] = [
    { href: "/admin/media", label: "مكتبة الميديا", icon: Images, perm: [MEDIA] },
    { href: "/admin/menus", label: "القوائم", icon: ListTree, perm: [CONTENT] },
    { href: "/admin/pages", label: "الصفحات", icon: FileText, perm: [CONTENT] },
    { href: "/admin/landing-pages", label: "صفحات الهبوط", icon: Search, perm: [CONTENT] },
];

// موديولات الدومين
const moduleNav: NavItem[] = [
    { href: "/admin/properties", label: "العقارات", icon: Building2, perm: [CATALOG, LISTINGS] },
    { href: "/admin/compounds", label: "الكمبوندات", icon: Building2, perm: [CATALOG, PROJECTS] },
    { href: "/admin/developers", label: "المطوّرون", icon: Briefcase, perm: [CATALOG] },
    { href: "/admin/locations", label: "المناطق", icon: MapPin, perm: [CATALOG] },
    { href: "/admin/featured-ads", label: "المساحات الإعلانية", icon: Megaphone, perm: [FEATURE] },
    { href: "/admin/leads", label: "الطلبات", icon: Inbox, perm: [LEADS] },
    { href: "/admin/posts", label: "المدونة", icon: Newspaper, perm: [CONTENT] },
];

// إدارة النظام
const systemNav: NavItem[] = [
    { href: "/admin/reports", label: "التقارير", icon: BarChart3, perm: [REPORTS] },
    { href: "/admin/users", label: "المستخدمون", icon: UserCog, perm: [USERS, ROLES] },
    { href: "/admin/activity", label: "سجل النشاط", icon: ScrollText, perm: [USERS] },
];

export default function AdminLayout({ title, children }: { title: string; children: ReactNode }) {
    const { auth } = usePage<SharedProps>().props;
    const path = typeof window !== "undefined" ? window.location.pathname : "";
    const can = auth.user?.can ?? [];

    // اللينك بيظهر بس لو المستخدم معاه صلاحية واحدة على الأقل — نفس التحقق موجود على الراوت
    const allowed = (items: NavItem[]) => items.filter((i) => i.perm.some((perm) => can.includes(perm)));

    const section = (title: string, items: NavItem[]) => {
        const visible = allowed(items);

        if (visible.length === 0) return null;

        return (
            <>
                <div className="mt-4 mb-1 px-4 text-[11px] font-extrabold tracking-wide text-gray-500">{title}</div>
                {visible.map((i) => item(i.href, i.label, i.icon, path.startsWith(i.href)))}
            </>
        );
    };

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
            <aside className="fixed inset-y-0 start-0 z-40 flex w-64 flex-col bg-bg-dark p-4">
                <div className="mb-8 flex items-center gap-2 px-2 pt-2">
                    <span className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary font-extrabold text-primary-fg">
                        BP
                    </span>
                    <span className="text-sm font-extrabold text-white">إنجن شريك الأعمال</span>
                </div>

                {/* min-h-0 مهم: من غيره الفلكس مبيسمحش للعنصر يقل عن محتواه فالسكرول مبيشتغلش */}
                <nav className="-mx-1 flex min-h-0 flex-1 flex-col gap-1 overflow-y-auto px-1">
                    {item("/admin", "لوحة التحكم", LayoutDashboard, path === "/admin")}

                    {section("الإعدادات", settingsNav)}
                    {section("المحتوى", contentNav)}
                    {section("الموديولات", moduleNav)}
                    {section("النظام", systemNav)}
                </nav>

                <div className="mt-4 shrink-0 border-t border-white/10 pt-4">
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
            <div className="flex-1 ps-64">
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
