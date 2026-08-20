import { Head, Link, usePage } from "@inertiajs/react";
import { Mail, Menu, MessageCircle, Phone, X as Close } from "lucide-react";
import { Facebook, Instagram, Linkedin, Snapchat, Tiktok, X, Youtube } from "@/Components/site/SocialIcons";
import { useState, type ReactNode } from "react";
import type { MenuLink, SharedProps } from "@/lib/types";

const socialIcons = [
    { key: "facebook", Icon: Facebook },
    { key: "instagram", Icon: Instagram },
    { key: "linkedin", Icon: Linkedin },
    { key: "youtube", Icon: Youtube },
    { key: "tiktok", Icon: Tiktok },
    { key: "x", Icon: X },
    { key: "snapchat", Icon: Snapchat },
] as const;

export default function SiteLayout({ children }: { children: ReactNode }) {
    const { settings, locale, menu, meta } = usePage<SharedProps>().props;
    const general = settings.general ?? {};
    const branding = settings.branding ?? {};
    const contact = settings.contact ?? {};
    const social = settings.social ?? {};
    const [open, setOpen] = useState(false);

    const ar = locale === "ar";
    const currentPath = typeof window !== "undefined" ? window.location.pathname : `/${locale}`;
    const otherLocale = ar ? "en" : "ar";
    const switchUrl = () => {
        const parts = window.location.pathname.split("/");
        parts[1] = otherLocale;
        return parts.join("/") || `/${otherLocale}`;
    };

    const isActive = (url: string) =>
        url === "/" ? currentPath === `/${locale}` : currentPath.startsWith(`/${locale}${url}`);

    // لينك القائمة: داخلي بيمشي على Inertia، وخارجي بيفضل <a> عادي
    const navLink = (item: MenuLink, className: string, onClick?: () => void) =>
        item.external ? (
            <a
                key={item.url}
                href={item.url}
                onClick={onClick}
                target={item.newTab ? "_blank" : undefined}
                rel={item.newTab ? "noreferrer" : undefined}
                className={className}
            >
                {item.label}
            </a>
        ) : (
            <Link
                key={item.url}
                href={`/${locale}${item.url === "/" ? "" : item.url}`}
                onClick={onClick}
                target={item.newTab ? "_blank" : undefined}
                className={className}
            >
                {item.label}
            </Link>
        );

    const year = new Date().getFullYear();

    return (
        <div className="flex min-h-screen flex-col bg-bg">
            {/* السيرفر بيرندر الميتا كاملة لمحركات البحث — ده بس عشان عنوان التاب
                يتغيّر وإنت بتتنقّل جوه الموقع من غير reload */}
            {meta?.title && <Head title={meta.title} />}

            {/* ---------- شريط علوي كحلي: تواصل سريع + سوشيال ---------- */}
            <div className="hidden bg-bg-dark text-white/70 md:block">
                <div className="mx-auto flex h-9 max-w-7xl items-center justify-between px-4 text-xs font-bold">
                    <div className="flex items-center gap-5">
                        {contact.phone && (
                            <a href={`tel:${contact.phone}`} className="flex items-center gap-1.5 transition hover:text-primary">
                                <Phone size={13} className="text-primary" />
                                <span dir="ltr">{contact.phone}</span>
                            </a>
                        )}
                        {contact.email && (
                            <a href={`mailto:${contact.email}`} className="flex items-center gap-1.5 transition hover:text-primary">
                                <Mail size={13} className="text-primary" />
                                <span dir="ltr">{contact.email}</span>
                            </a>
                        )}
                    </div>

                    <div className="flex items-center gap-3">
                        {socialIcons.map(({ key, Icon }) =>
                            social[key] ? (
                                <a
                                    key={key}
                                    href={social[key]}
                                    target="_blank"
                                    rel="noreferrer"
                                    aria-label={key}
                                    className="transition hover:text-primary"
                                >
                                    <Icon size={14} />
                                </a>
                            ) : null,
                        )}
                        <span className="text-white/40">{ar ? "السبت – الخميس · 10ص – 8م" : "Sat – Thu · 10am – 8pm"}</span>
                    </div>
                </div>
            </div>

            {/* ------------------------------ Header ------------------------------ */}
            <header className="sticky top-0 z-40 border-b border-gray-100 bg-bg/95 backdrop-blur-md">
                <div className="mx-auto flex h-[68px] max-w-7xl items-center justify-between gap-6 px-4">
                    <Link href={`/${locale}`} className="flex shrink-0 items-center gap-3">
                        {branding.logo_path && (
                            <img src={branding.logo_path} alt={general.site_name ?? ""} className="h-11 w-auto" />
                        )}
                        <span className="hidden flex-col items-start leading-tight sm:flex">
                            <span className="text-lg font-black text-secondary">{general.site_name || "BP Engine"}</span>
                            {general.tagline && (
                                <span className="text-[11px] font-bold text-muted">{general.tagline}</span>
                            )}
                        </span>
                    </Link>

                    <nav className="hidden items-center gap-1 lg:flex">
                        {menu.header.map((item) => {
                            const active = !item.external && isActive(item.url);

                            return (
                                <span key={item.url} className="relative">
                                    {navLink(
                                        item,
                                        `relative block px-4 py-3 text-sm font-extrabold transition ${
                                            active ? "text-secondary" : "text-secondary/70 hover:text-primary"
                                        }`,
                                    )}
                                    {/* أندرلاين دهبي للصفحة الحالية */}
                                    <span
                                        className={`pointer-events-none absolute inset-x-4 bottom-1.5 h-0.5 rounded-full bg-primary transition-opacity ${
                                            active ? "opacity-100" : "opacity-0"
                                        }`}
                                    />
                                </span>
                            );
                        })}
                    </nav>

                    <div className="flex items-center gap-2.5">
                        <button
                            onClick={() => (window.location.href = switchUrl())}
                            className="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-extrabold text-secondary transition hover:border-primary hover:bg-primary/10 hover:text-primary"
                        >
                            {otherLocale.toUpperCase()}
                        </button>

                        {contact.whatsapp && (
                            <a
                                href={`https://wa.me/${contact.whatsapp}`}
                                target="_blank"
                                rel="noreferrer"
                                className="hidden items-center gap-2 rounded-brand border-2 border-primary px-4 py-2 text-sm font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg md:flex"
                            >
                                <MessageCircle size={15} />
                                {ar ? "واتساب" : "WhatsApp"}
                            </a>
                        )}

                        <Link
                            href={`/${locale}/contact`}
                            className="hidden rounded-brand bg-primary px-5 py-2.5 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover sm:block"
                        >
                            {ar ? "احجز معاينة" : "Book a viewing"}
                        </Link>

                        <button
                            onClick={() => setOpen(!open)}
                            className="text-secondary lg:hidden"
                            aria-label={ar ? "القائمة" : "Menu"}
                        >
                            {open ? <Close size={22} /> : <Menu size={22} />}
                        </button>
                    </div>
                </div>

                {open && (
                    <nav className="border-t border-gray-100 bg-bg px-4 py-3 lg:hidden">
                        {menu.header.map((item) =>
                            navLink(
                                item,
                                `block rounded-xl px-4 py-3 text-sm font-extrabold transition ${
                                    !item.external && isActive(item.url)
                                        ? "bg-primary/10 text-secondary"
                                        : "text-secondary/70 hover:bg-surface hover:text-primary"
                                }`,
                                () => setOpen(false),
                            ),
                        )}
                    </nav>
                )}
            </header>

            <main className="flex-1">{children}</main>

            {/* ------------------------------ Footer (داكن — زي التصميم) ------------------------------ */}
            <footer className="bg-bg-dark">
                <div className="mx-auto max-w-7xl px-4 py-12">
                    <div className="grid gap-10 md:grid-cols-[1.5fr_1fr_1.2fr]">
                        <div>
                            <div className="flex items-center gap-3">
                                {branding.logo_path && (
                                    <span className="flex h-13 w-13 items-center justify-center rounded-xl bg-bg p-2">
                                        <img src={branding.logo_path} alt="" className="h-9 w-9 object-contain" />
                                    </span>
                                )}
                                <span className="text-lg font-black text-text-dark">{general.site_name || "BP Engine"}</span>
                            </div>
                            {general.tagline && (
                                <p className="mt-4 max-w-xs text-sm leading-[1.9] text-white/55">{general.tagline}</p>
                            )}
                            <div className="mt-5 flex items-center gap-3">
                                {socialIcons.map(({ key, Icon }) =>
                                    social[key] ? (
                                        <a
                                            key={key}
                                            href={social[key]}
                                            target="_blank"
                                            rel="noreferrer"
                                            aria-label={key}
                                            className="flex h-9 w-9 items-center justify-center rounded-full border border-white/15 text-white/60 transition hover:border-primary hover:bg-primary hover:text-primary-fg"
                                        >
                                            <Icon size={15} />
                                        </a>
                                    ) : null,
                                )}
                            </div>
                        </div>

                        <div className="flex flex-col gap-3">
                            <span className="text-[13px] font-extrabold text-text-dark">{ar ? "الموقع" : "Site"}</span>
                            {menu.footer.map((item) =>
                                navLink(item, "text-sm text-white/55 transition hover:text-primary"),
                            )}
                        </div>

                        <div className="flex flex-col gap-3">
                            <span className="text-[13px] font-extrabold text-text-dark">{ar ? "تواصل" : "Contact"}</span>
                            {contact.phone && (
                                <a href={`tel:${contact.phone}`} className="text-sm text-white/55 transition hover:text-primary">
                                    <span dir="ltr">{contact.phone}</span>
                                </a>
                            )}
                            {contact.email && (
                                <a href={`mailto:${contact.email}`} className="text-sm text-white/55 transition hover:text-primary">
                                    <span dir="ltr">{contact.email}</span>
                                </a>
                            )}
                            {contact.address && <span className="text-sm leading-relaxed text-white/55">{contact.address}</span>}
                        </div>

                    </div>

                    {/* سطر الحقوق الإلزامي — شريك الأعمال (لا يُحذف من أي موقع خارج من الإنجن) */}
                    <div className="mt-10 border-t border-white/10 pt-6 text-center text-xs leading-relaxed text-white/45">
                        <div dir="ltr">
                            © {year}{" "}
                            <a href="https://bp-eg.com" className="transition hover:text-primary hover:underline">
                                Business Partner for Information Technology
                            </a>
                            . All rights reserved.
                        </div>
                        <div dir="rtl">
                            © {year}{" "}
                            <a href="https://bp-eg.com" className="transition hover:text-primary hover:underline">
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
