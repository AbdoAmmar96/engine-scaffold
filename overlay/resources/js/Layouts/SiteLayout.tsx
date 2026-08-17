import { Link, usePage } from "@inertiajs/react";
import { Menu, X } from "lucide-react";
import { useState, type ReactNode } from "react";
import type { SharedProps } from "@/lib/types";

const navItems = [
    { path: "", ar: "الرئيسية", en: "Home" },
    { path: "/properties", ar: "العقارات", en: "Properties" },
    { path: "/compounds", ar: "الكمبوندات", en: "Compounds" },
    { path: "/about", ar: "من نحن", en: "About" },
    { path: "/contact", ar: "اتصل بنا", en: "Contact" },
];

export default function SiteLayout({ children }: { children: ReactNode }) {
    const { settings, locale } = usePage<SharedProps>().props;
    const general = settings.general ?? {};
    const branding = settings.branding ?? {};
    const contact = settings.contact ?? {};
    const [open, setOpen] = useState(false);

    const currentPath = typeof window !== "undefined" ? window.location.pathname : `/${locale}`;
    const otherLocale = locale === "ar" ? "en" : "ar";
    const switchUrl = () => {
        const parts = window.location.pathname.split("/");
        parts[1] = otherLocale;
        return parts.join("/") || `/${otherLocale}`;
    };

    const isActive = (path: string) =>
        path === "" ? currentPath === `/${locale}` : currentPath.startsWith(`/${locale}${path}`);

    const year = new Date().getFullYear();

    const NavLinks = ({ mobile = false }: { mobile?: boolean }) => (
        <>
            {navItems.map((item) => (
                <Link
                    key={item.path}
                    href={`/${locale}${item.path}`}
                    onClick={() => setOpen(false)}
                    className={`${mobile ? "block rounded-xl px-4 py-3" : ""} text-sm font-bold transition ${
                        isActive(item.path) ? "text-secondary" : "text-muted hover:text-secondary"
                    }`}
                >
                    {locale === "ar" ? item.ar : item.en}
                </Link>
            ))}
        </>
    );

    return (
        <div className="flex min-h-screen flex-col bg-bg">
            {/* ------------------------------ Header ------------------------------ */}
            <header className="sticky top-0 z-40 border-b border-gray-100 bg-bg/90 backdrop-blur">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4">
                    <Link href={`/${locale}`} className="flex items-center gap-2">
                        {branding.logo_path ? (
                            <img src={branding.logo_path} alt={general.site_name ?? ""} className="h-10 w-auto" />
                        ) : (
                            <span className="text-xl font-extrabold text-secondary">
                                {general.site_name || "BP Engine"}
                            </span>
                        )}
                    </Link>

                    <nav className="hidden items-center gap-6 md:flex">
                        <NavLinks />
                    </nav>

                    <div className="flex items-center gap-3">
                        <a
                            href="#"
                            onClick={(e) => {
                                e.preventDefault();
                                window.location.href = switchUrl();
                            }}
                            className="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-extrabold text-muted hover:border-primary hover:text-primary"
                        >
                            {otherLocale.toUpperCase()}
                        </a>
                        {contact.whatsapp && (
                            <a
                                href={`https://wa.me/${contact.whatsapp}`}
                                target="_blank"
                                rel="noreferrer"
                                className="hidden rounded-brand bg-primary px-4 py-2 text-sm font-extrabold text-primary-fg hover:bg-primary-hover sm:block"
                            >
                                {locale === "ar" ? "كلّمنا واتساب" : "WhatsApp us"}
                            </a>
                        )}
                        <button
                            onClick={() => setOpen(!open)}
                            className="text-secondary md:hidden"
                            aria-label={locale === "ar" ? "القائمة" : "Menu"}
                        >
                            {open ? <X size={22} /> : <Menu size={22} />}
                        </button>
                    </div>
                </div>

                {open && (
                    <nav className="border-t border-gray-100 bg-bg px-4 py-3 md:hidden">
                        <NavLinks mobile />
                    </nav>
                )}
            </header>

            <main className="flex-1">{children}</main>

            {/* ------------------------------ Footer (فاتح) ------------------------------ */}
            <footer className="border-t border-gray-100 bg-surface">
                <div className="mx-auto max-w-7xl px-4 py-12">
                    <div className="grid gap-10 md:grid-cols-3">
                        <div>
                            <span className="text-lg font-extrabold text-secondary">{general.site_name || "BP Engine"}</span>
                            {general.tagline && <p className="mt-2 max-w-xs text-sm leading-relaxed text-muted">{general.tagline}</p>}
                        </div>
                        <div>
                            <span className="text-sm font-extrabold text-secondary">{locale === "ar" ? "روابط سريعة" : "Quick links"}</span>
                            <div className="mt-3 flex flex-col gap-2">
                                {navItems.slice(1).map((item) => (
                                    <Link key={item.path} href={`/${locale}${item.path}`} className="text-sm text-muted hover:text-secondary">
                                        {locale === "ar" ? item.ar : item.en}
                                    </Link>
                                ))}
                            </div>
                        </div>
                        <div>
                            <span className="text-sm font-extrabold text-secondary">{locale === "ar" ? "تواصل معنا" : "Get in touch"}</span>
                            <div className="mt-3 flex flex-col gap-2 text-sm text-muted">
                                {contact.phone && <span dir="ltr">{contact.phone}</span>}
                                {contact.email && <span dir="ltr">{contact.email}</span>}
                                {contact.address && <span>{contact.address}</span>}
                            </div>
                        </div>
                    </div>

                    {/* سطر الحقوق الإلزامي — شريك الأعمال (لا يُحذف من أي موقع خارج من الإنجن) */}
                    <div className="mt-10 border-t border-gray-200 pt-6 text-center text-xs leading-relaxed text-muted">
                        <div dir="ltr">
                            © {year}{" "}
                            <a href="https://bp-eg.com" className="underline-offset-2 hover:underline">
                                Business Partner for Information Technology
                            </a>
                            . All rights reserved.
                        </div>
                        <div dir="rtl">
                            © {year}{" "}
                            <a href="https://bp-eg.com" className="underline-offset-2 hover:underline">
                                شركة شريك الأعمال لتقنية المعلومات
                            </a>
                            . جميع الحقوق محفوظة.
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
}
