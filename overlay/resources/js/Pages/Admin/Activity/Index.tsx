import { router } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Card, Input } from "@/Components/admin/ui";
import type { Paginated } from "@/lib/types";

interface Row {
    id: number;
    user: string;
    action: { label: string; tone: string };
    subject: string;
    label: string;
    changed: string[];
    ip: string;
    at: string | null;
}

const tones: Record<string, string> = {
    success: "bg-success/10 text-success",
    primary: "bg-primary/10 text-primary",
    danger: "bg-danger/10 text-danger",
    muted: "bg-gray-100 text-gray-500",
};

const select =
    "rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/25";

/**
 * سجل النشاط — قراءة بس.
 *
 * بيسجّل أفعال الناس في اللوحة: مين غيّر إيه وإمتى ومن أي IP. أسماء
 * الحقول اللي اتغيّرت بتتعرض من غير قيمها — القيم فيها بيانات عملاء
 * وهاشات مش المفروض تتفرّج من شاشة.
 */
export default function Activity({
    rows,
    filters,
    options,
}: {
    rows: Paginated<Row>;
    filters: { action: string; subject: string; q: string };
    options: { actions: { value: string; label: string }[]; subjects: { value: string; label: string }[] };
}) {
    const go = (next: Partial<typeof filters>) =>
        router.get("/admin/activity", { ...filters, ...next }, { preserveState: true, replace: true });

    return (
        <AdminLayout title="سجل النشاط">
            <Card>
                <div className="mb-5 flex flex-wrap items-center gap-3">
                    <Input
                        placeholder="ابحث باسم المستخدم أو الصف…"
                        defaultValue={filters.q}
                        onKeyDown={(e) => e.key === "Enter" && go({ q: (e.target as HTMLInputElement).value })}
                        className="max-w-xs"
                    />

                    <select value={filters.action} onChange={(e) => go({ action: e.target.value })} className={select}>
                        <option value="">كل الأفعال</option>
                        {options.actions.map((o) => (
                            <option key={o.value} value={o.value}>
                                {o.label}
                            </option>
                        ))}
                    </select>

                    <select value={filters.subject} onChange={(e) => go({ subject: e.target.value })} className={select}>
                        <option value="">كل الكيانات</option>
                        {options.subjects.map((o) => (
                            <option key={o.value} value={o.value}>
                                {o.label}
                            </option>
                        ))}
                    </select>

                    <span className="ms-auto text-[12px] font-bold text-gray-500">
                        {rows.total} سطر
                    </span>
                </div>

                {rows.data.length === 0 ? (
                    <p className="py-10 text-center text-sm text-gray-400">لا يوجد نشاط مسجّل بهذه الفلاتر</p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[46rem] text-[13px]">
                            <thead>
                                <tr className="text-gray-500">
                                    <th className="pb-2 text-start font-bold">المستخدم</th>
                                    <th className="pb-2 text-start font-bold">الإجراء</th>
                                    <th className="pb-2 text-start font-bold">العنصر</th>
                                    <th className="pb-2 text-start font-bold">الحقول</th>
                                    <th className="pb-2 text-start font-bold">التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.data.map((r) => (
                                    <tr key={r.id} className="border-t border-gray-100 align-top">
                                        <td className="py-2.5 font-bold text-gray-800">
                                            {r.user}
                                            {r.ip && <span dir="ltr" className="block text-[11px] font-normal text-gray-400">{r.ip}</span>}
                                        </td>
                                        <td className="py-2.5">
                                            <span className={`rounded-full px-2.5 py-1 text-[11px] font-extrabold ${tones[r.action.tone] ?? tones.muted}`}>
                                                {r.action.label}
                                            </span>
                                        </td>
                                        <td className="max-w-[18rem] py-2.5">
                                            <span className="text-gray-500">{r.subject}:</span>{" "}
                                            <span className="font-bold text-gray-800">{r.label}</span>
                                        </td>
                                        <td className="max-w-[16rem] py-2.5">
                                            {r.changed.length > 0 ? (
                                                <span dir="ltr" className="block truncate text-[11px] text-gray-500" title={r.changed.join(", ")}>
                                                    {r.changed.join(", ")}
                                                </span>
                                            ) : (
                                                <span className="text-gray-300">—</span>
                                            )}
                                        </td>
                                        <td dir="ltr" className="py-2.5 text-gray-500">{r.at}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {rows.links.length > 3 && (
                    <div className="mt-5 flex flex-wrap gap-1.5">
                        {rows.links.map((link) => (
                            <button
                                key={link.label}
                                type="button"
                                disabled={!link.url}
                                onClick={() => link.url && router.get(link.url, {}, { preserveState: true })}
                                className={`rounded-lg px-3 py-1.5 text-[12px] font-bold transition ${
                                    link.active
                                        ? "bg-primary text-primary-fg"
                                        : "border border-gray-200 text-gray-600 hover:border-primary hover:text-primary disabled:opacity-40"
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </Card>
        </AdminLayout>
    );
}
