import { Link, usePage } from "@inertiajs/react";
import { ChevronDown } from "lucide-react";
import { useEffect, useRef, useState } from "react";
import type { MenuLink, SharedProps } from "@/lib/types";

/**
 * قائمة الهيدر على الديسكتوب — بتدعم مستوى واحد من القوائم المنسدلة.
 * العنصر اللي مالوش url بيبقى عنوان قايمة بس.
 */
export default function NavMenu({ items }: { items: MenuLink[] }) {
    const { locale } = usePage<SharedProps>().props;
    const [open, setOpen] = useState<string | null>(null);
    const wrap = useRef<HTMLElement>(null);

    const path = typeof window !== "undefined" ? window.location.pathname : `/${locale}`;

    const href = (item: MenuLink) => (item.external ? item.url : `/${locale}${item.url === "/" ? "" : item.url}`);

    const isActive = (item: MenuLink): boolean => {
        if (item.children.length > 0) return item.children.some(isActive);
        if (item.external) return false;

        return item.url === "/" ? path === `/${locale}` : path.startsWith(`/${locale}${item.url}`);
    };

    // إغلاق بالضغط بره أو بـ Escape — القايمة المفتوحة على الشاشة كلها مزعجة
    useEffect(() => {
        if (!open) return;

        const onDown = (e: MouseEvent) => {
            if (!wrap.current?.contains(e.target as Node)) setOpen(null);
        };
        const onKey = (e: KeyboardEvent) => e.key === "Escape" && setOpen(null);

        document.addEventListener("mousedown", onDown);
        document.addEventListener("keydown", onKey);

        return () => {
            document.removeEventListener("mousedown", onDown);
            document.removeEventListener("keydown", onKey);
        };
    }, [open]);

    const linkClass = (active: boolean) =>
        `relative block whitespace-nowrap rounded-xl px-3.5 py-2.5 text-[14px] font-extrabold transition ${
            active ? "text-secondary" : "text-secondary/70 hover:bg-surface hover:text-primary"
        }`;

    const underline = (active: boolean) => (
        <span
            aria-hidden
            className={`pointer-events-none absolute inset-x-3.5 -bottom-1 h-0.5 rounded-full bg-primary transition-opacity ${
                active ? "opacity-100" : "opacity-0"
            }`}
        />
    );

    return (
        <nav ref={wrap} className="hidden items-center gap-2 xl:flex">
            {items.map((item) => {
                const active = isActive(item);

                if (item.children.length === 0) {
                    return item.external ? (
                        <a
                            key={item.label}
                            href={item.url}
                            target={item.newTab ? "_blank" : undefined}
                            rel={item.newTab ? "noreferrer" : undefined}
                            className={linkClass(active)}
                        >
                            {item.label}
                        </a>
                    ) : (
                        <Link key={item.label} href={href(item)} className={linkClass(active)}>
                            {item.label}
                            {underline(active)}
                        </Link>
                    );
                }

                const isOpen = open === item.label;

                return (
                    <div
                        key={item.label}
                        className="relative"
                        onMouseEnter={() => setOpen(item.label)}
                        onMouseLeave={() => setOpen(null)}
                    >
                        <button
                            type="button"
                            aria-haspopup="true"
                            aria-expanded={isOpen}
                            onClick={() => setOpen(isOpen ? null : item.label)}
                            className={`${linkClass(active)} flex items-center gap-1.5`}
                        >
                            {item.label}
                            <ChevronDown
                                size={14}
                                className={`text-primary transition-transform ${isOpen ? "rotate-180" : ""}`}
                            />
                            {underline(active)}
                        </button>

                        {isOpen && (
                            <div className="absolute top-full start-0 z-50 min-w-56 rounded-2xl border border-gray-100 bg-bg p-2 shadow-[0_16px_40px_rgba(11,18,32,0.12)]">
                                {item.children.map((child) => {
                                    const childActive = isActive(child);

                                    return (
                                        <Link
                                            key={child.label}
                                            href={href(child)}
                                            onClick={() => setOpen(null)}
                                            className={`block whitespace-nowrap rounded-xl px-4 py-2.5 text-[13px] font-extrabold transition ${
                                                childActive
                                                    ? "bg-primary/10 text-primary"
                                                    : "text-secondary/80 hover:bg-surface hover:text-primary"
                                            }`}
                                        >
                                            {child.label}
                                        </Link>
                                    );
                                })}
                            </div>
                        )}
                    </div>
                );
            })}
        </nav>
    );
}
