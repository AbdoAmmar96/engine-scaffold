import { Head, Link, usePage } from "@inertiajs/react";
import { LayoutDashboard, Mail, Menu, MessageCircle, Phone, UserRound, X as Close } from "lucide-react";
import { Facebook, Instagram, Linkedin, Snapchat, Tiktok, X, Youtube } from "@/Components/site/SocialIcons";
import NavMenu from "@/Components/site/NavMenu";
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
    const { settings, locale, menu, meta, auth } = usePage<SharedProps>().props;
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

    // الفوتر بيتقسم لأعمدة: اللي له أبناء بيبقى مجموعة بعنوانها،
    // والباقي بيتجمّع تحت «الموقع»
    const footerGroups = menu.footer.filter((item) => item.children.length > 0);
    const footerLinks = menu.footer.filter((item) => item.children.length === 0);

    // عمود بعنوان وتحته فراغ أسوأ من عمود مش موجود
    const hasContact = Boolean(contact.phone || contact.email || contact.address);

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
                <div className="mx-auto flex h-[68px] max-w-7xl items-center gap-4 px-4">
                    <Link href={`/${locale}`} className="flex min-w-0 shrink-0 items-center gap-3">
                        {branding.logo_path && (
                            <img src={branding.logo_path} alt={general.site_name ?? ""} className="h-11 w-auto" />
                        )}
                        {/* min-w-0 + truncate: اسم طويل كان بيدفع القائمة ويطفح الهيدر */}
                        <span className="hidden min-w-0 flex-col items-start leading-tight sm:flex">
                            <span className="truncate text-lg font-black text-secondary">
                                {general.site_name || "BP Engine"}
                            </span>
                            {general.tagline && (
                                <span className="truncate text-[11px] font-bold text-muted">{general.tagline}</span>
                            )}
                        </span>
                    </Link>

                    <NavMenu items={menu.header} />

                    <div className="flex shrink-0 items-center gap-2.5">
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
                                className="hidden items-center gap-2 whitespace-nowrap rounded-brand border-2 border-primary px-4 py-2 text-sm font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg md:flex"
                            >
                                <MessageCircle size={15} />
                                {ar ? "واتساب" : "WhatsApp"}
                            </a>
                        )}

                        {/* الموظف بيروح اللوحة، العميل بيروح حسابه، والضيف بيسجّل */}
                        {auth.user ? (
                            <Link
                                href={auth.user.staff ? "/admin" : `/${locale}/account`}
                                className="hidden items-center gap-2 whitespace-nowrap rounded-brand border border-gray-200 px-4 py-2 text-sm font-extrabold text-secondary transition hover:border-primary hover:text-primary sm:flex"
                            >
                                {auth.user.staff ? <LayoutDashboard size={15} /> : <UserRound size={15} />}
                                {auth.user.staff ? (ar ? "لوحة التحكم" : "Dashboard") : ar ? "حسابي" : "My account"}
                            </Link>
                        ) : (
                            <Link
                                href={`/${locale}/login`}
                                className="hidden items-center gap-2 whitespace-nowrap rounded-brand border border-gray-200 px-4 py-2 text-sm font-extrabold text-secondary transition hover:border-primary hover:text-primary sm:flex"
                            >
                                <UserRound size={15} />
                                {ar ? "دخول" : "Sign in"}
                            </Link>
                        )}

                        <Link
                            href={`/${locale}/contact`}
                            className="hidden whitespace-nowrap rounded-brand bg-primary px-5 py-2.5 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover sm:block"
                        >
                            {ar ? "احجز معاينة" : "Book a viewing"}
                        </Link>

                        <button
                            onClick={() => setOpen(!open)}
                            className="text-secondary xl:hidden"
                            aria-label={ar ? "القائمة" : "Menu"}
                        >
                            {open ? <Close size={22} /> : <Menu size={22} />}
                        </button>
                    </div>
                </div>

                {open && (
                    <nav className="border-t border-gray-100 bg-bg px-4 py-3 xl:hidden">
                        {menu.header.map((item) =>
                            item.children.length > 0 ? (
                                // العنصر الأب على الموبايل بيتفتح كمجموعة، مش قايمة منسدلة
                                <div key={item.label} className="py-1">
                                    <span className="block px-4 py-2 text-[11px] font-extrabold tracking-wide text-muted">
                                        {item.label}
                                    </span>
                                    {item.children.map((child) =>
                                        navLink(
                                            child,
                                            `block rounded-xl px-4 py-3 text-sm font-extrabold transition ps-8 ${
                                                !child.external && isActive(child.url)
                                                    ? "bg-primary/10 text-secondary"
                                                    : "text-secondary/70 hover:bg-surface hover:text-primary"
                                            }`,
                                            () => setOpen(false),
                                        ),
                                    )}
                                </div>
                            ) : (
                                navLink(
                                    item,
                                    `block rounded-xl px-4 py-3 text-sm font-extrabold transition ${
                                        !item.external && isActive(item.url)
                                            ? "bg-primary/10 text-secondary"
                                            : "text-secondary/70 hover:bg-surface hover:text-primary"
                                    }`,
                                    () => setOpen(false),
                                )
                            ),
                        )}

                        <Link
                            href={auth.user ? (auth.user.staff ? "/admin" : `/${locale}/account`) : `/${locale}/login`}
                            onClick={() => setOpen(false)}
                            className="mt-1 flex items-center gap-2 rounded-xl border-t border-gray-100 px-4 py-3 text-sm font-extrabold text-secondary"
                        >
                            <UserRound size={15} className="text-primary" />
                            {auth.user
                                ? auth.user.staff
                                    ? ar
                                        ? "لوحة التحكم"
                                        : "Dashboard"
                                    : ar
                                      ? "حسابي"
                                      : "My account"
                                : ar
                                  ? "دخول / حساب جديد"
                                  : "Sign in / Register"}
                        </Link>
                    </nav>
                )}
            </header>

            <main className="flex-1">{children}</main>

            {/* ------------------------------ Footer (داكن — زي التصميم) ------------------------------ */}
            <footer className="bg-bg-dark">
                <div className="mx-auto max-w-7xl px-4 py-12">
                    <div className="grid gap-10 sm:grid-cols-2 lg:grid-cols-[1.6fr_repeat(var(--footer-cols),minmax(0,1fr))]" style={{ ["--footer-cols" as string]: footerGroups.length + (footerLinks.length > 0 ? 1 : 0) + (hasContact ? 1 : 0) }}>
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

                        {/* عنصر أب بأبناء بيبقى عمود بعنوانه، واللي من غير أبناء
                            بيتجمّعوا في عمود «الموقع» — نفس بيانات القوائم بتاعة
                            الهيدر، فالأدمن بيرتّبها من /admin/menus من غير كود */}
                        {footerGroups.map((group) => (
                            <div key={group.label} className="flex min-w-0 flex-col gap-3">
                                <span className="text-[13px] font-extrabold text-text-dark">{group.label}</span>
                                {group.children.map((child) =>
                                    navLink(child, "text-sm text-white/55 transition hover:text-primary"),
                                )}
                            </div>
                        ))}

                        {footerLinks.length > 0 && (
                            <div className="flex min-w-0 flex-col gap-3">
                                <span className="text-[13px] font-extrabold text-text-dark">{ar ? "الموقع" : "Site"}</span>
                                {footerLinks.map((item) =>
                                    navLink(item, "text-sm text-white/55 transition hover:text-primary"),
                                )}
                            </div>
                        )}

                        {hasContact && (
                        <div className="flex min-w-0 flex-col gap-3">
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
                        )}

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
