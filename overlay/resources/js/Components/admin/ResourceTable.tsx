import { Link, router } from "@inertiajs/react";
import { Search } from "lucide-react";
import { useState, type ReactNode } from "react";
import type { Paginated } from "@/lib/types";

/**
 * ResourceTable v0 — جدول عام server-driven:
 * البحث والفرز والباجينيشن كلها query params بتتنفذ في Laravel.
 * كل CRUD في الموديولات الجاية (عقارات/كمبوندات/مطوّرين...) هيستخدمه زي ما هو.
 */

export interface Column<T> {
    key: string;
    label: string;
    render?: (row: T) => ReactNode;
    className?: string;
}

export default function ResourceTable<T extends { id: number | string }>({
    columns,
    paginator,
    searchable = true,
    searchPlaceholder = "بحث…",
    empty = "لا توجد بيانات بعد",
    actions,
}: {
    columns: Column<T>[];
    paginator: Paginated<T>;
    searchable?: boolean;
    searchPlaceholder?: string;
    empty?: string;
    actions?: (row: T) => ReactNode;
}) {
    const params = new URLSearchParams(window.location.search);
    const [q, setQ] = useState(params.get("q") ?? "");

    const submitSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(window.location.pathname, q ? { q } : {}, { preserveState: true, replace: true });
    };

    return (
        <div className="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            {searchable && (
                <form onSubmit={submitSearch} className="flex items-center gap-2 border-b border-gray-100 px-4 py-3">
                    <Search size={16} className="text-gray-400" />
                    <input
                        value={q}
                        onChange={(e) => setQ(e.target.value)}
                        placeholder={searchPlaceholder}
                        className="w-full max-w-xs bg-transparent text-sm outline-none"
                    />
                </form>
            )}

            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-start text-xs font-extrabold text-gray-500">
                            {columns.map((c) => (
                                <th key={c.key} className={`px-4 py-3 text-start ${c.className ?? ""}`}>
                                    {c.label}
                                </th>
                            ))}
                            {actions && <th className="px-4 py-3" />}
                        </tr>
                    </thead>
                    <tbody>
                        {paginator.data.length === 0 && (
                            <tr>
                                <td colSpan={columns.length + (actions ? 1 : 0)} className="px-4 py-10 text-center text-gray-400">
                                    {empty}
                                </td>
                            </tr>
                        )}
                        {paginator.data.map((row) => (
                            <tr key={row.id} className="border-t border-gray-100 hover:bg-gray-50/60">
                                {columns.map((c) => (
                                    <td key={c.key} className={`px-4 py-3 ${c.className ?? ""}`}>
                                        {c.render ? c.render(row) : String((row as Record<string, unknown>)[c.key] ?? "—")}
                                    </td>
                                ))}
                                {actions && <td className="px-4 py-3 text-end">{actions(row)}</td>}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {paginator.links.length > 3 && (
                <div className="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 px-4 py-3">
                    <span className="text-xs text-gray-400">
                        {paginator.from ?? 0}–{paginator.to ?? 0} من {paginator.total}
                    </span>
                    <div className="flex flex-wrap gap-1">
                        {paginator.links.map((l, i) =>
                            l.url ? (
                                <Link
                                    key={i}
                                    href={l.url}
                                    preserveState
                                    className={`rounded-lg px-3 py-1.5 text-xs font-bold ${
                                        l.active ? "bg-primary text-primary-fg" : "text-gray-600 hover:bg-gray-100"
                                    }`}
                                    dangerouslySetInnerHTML={{ __html: l.label }}
                                />
                            ) : (
                                <span key={i} className="px-3 py-1.5 text-xs text-gray-300" dangerouslySetInnerHTML={{ __html: l.label }} />
                            ),
                        )}
                    </div>
                </div>
            )}
        </div>
    );
}
