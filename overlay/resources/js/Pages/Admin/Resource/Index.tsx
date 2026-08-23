import { Link, router } from "@inertiajs/react";
import { Check, Lock, Pencil, Plus, Trash2, X } from "lucide-react";
import { Button } from "@/Components/admin/ui";
import ResourceTable, { type Column } from "@/Components/admin/ResourceTable";
import AdminLayout from "@/Layouts/AdminLayout";
import type { Paginated, ResourceSchema } from "@/lib/types";

type Row = { id: number } & Record<string, unknown>;

/** الكنترولر يقدر يرجّع {label, tone} بدل نص عادي فيتحوّل لشارة ملوّنة */
type Chip = { label: string; tone: keyof typeof toneStyles };

const toneStyles = {
    primary: "bg-primary/10 text-primary",
    success: "bg-success/10 text-success",
    warn: "bg-amber-100 text-amber-700",
    muted: "bg-gray-100 text-gray-500",
    danger: "bg-danger/10 text-danger",
};

const isChip = (v: unknown): v is Chip =>
    typeof v === "object" && v !== null && "label" in v && "tone" in v;

/**
 * شاشة قائمة عامة لأي ريسورس — بتتبني من schema الكنترولر،
 * فمفيش صفحة مخصوصة لكل موديول.
 */
export default function ResourceIndex({
    resource,
    rows,
    activeFilters = {},
}: {
    resource: ResourceSchema;
    rows: Paginated<Row>;
    /** قيم الفلاتر الشغّالة دلوقتي — جاية من الراوت */
    activeFilters?: Record<string, string>;
}) {
    const columns: Column<Row>[] = Object.entries(resource.columns).map(([key, label], i) => ({
        key,
        label,
        render: (row) => {
            const v = row[key];

            // _self بيتبعت من الكنترولر عشان الصف بتاع حسابك يبان
            if (i === 0 && row._self) {
                return (
                    <span className="flex items-center gap-2">
                        <span className="line-clamp-1">{String(v ?? "—")}</span>
                        <span className="shrink-0 rounded-full bg-secondary/10 px-2 py-0.5 text-[10px] font-extrabold text-secondary">
                            أنت
                        </span>
                    </span>
                );
            }

            if (isChip(v)) {
                return (
                    <span
                        className={`inline-flex rounded-full px-2.5 py-1 text-[11px] font-extrabold ${
                            toneStyles[v.tone] ?? toneStyles.muted
                        }`}
                    >
                        {v.label}
                    </span>
                );
            }

            if (typeof v === "boolean") {
                return v ? (
                    <span className="inline-flex items-center gap-1 rounded-full bg-success/10 px-2.5 py-1 text-[11px] font-extrabold text-success">
                        <Check size={12} /> نعم
                    </span>
                ) : (
                    <span className="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-extrabold text-gray-500">
                        <X size={12} /> لا
                    </span>
                );
            }

            if (key === "purpose") return v === "rent" ? "إيجار" : "بيع";

            const text = v === null || v === undefined || v === "" ? "—" : String(v);

            // dir="auto" بيخلّي القيم الرقمية (تواريخ/أرقام موبايل) تتعرض LTR
            // والنص العربي يفضل RTL — من غير ما الجدول يعرف نوع العمود
            return (
                <span dir="auto" className="line-clamp-1">
                    {text}
                </span>
            );
        },
    }));

    // الفلتر بيمشي في الرابط عشان الصفحة تفضل قابلة للمشاركة والرجوع
    const filterBy = (name: string, value: string) => {
        const next = { ...activeFilters, [name]: value };
        const params = Object.fromEntries(Object.entries(next).filter(([, v]) => v));

        router.get(`/admin/${resource.key}`, params, { preserveScroll: true, preserveState: true });
    };

    const remove = (row: Row) => {
        if (!confirm(`متأكد من حذف "${row.name ?? row.title ?? row.id}"؟`)) return;
        router.delete(`/admin/${resource.key}/${row.id}`, { preserveScroll: true });
    };

    return (
        <AdminLayout title={resource.labels.plural}>
            <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div className="flex flex-wrap items-center gap-3">
                    <p className="text-sm text-gray-500">
                        إجمالي <span className="font-extrabold text-gray-800">{rows.total}</span> {resource.labels.singular}
                    </p>

                    {(resource.filters ?? []).map((f) => (
                        <label key={f.name} className="flex items-center gap-2 text-xs font-bold text-gray-500">
                            {f.label}
                            <select
                                value={activeFilters[f.name] ?? ""}
                                onChange={(e) => filterBy(f.name, e.target.value)}
                                className="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-bold text-gray-800 outline-none transition focus:border-primary"
                            >
                                <option value="">الكل</option>
                                {f.options.map((o) => (
                                    <option key={o.value} value={o.value}>
                                        {o.label}
                                    </option>
                                ))}
                            </select>
                        </label>
                    ))}
                </div>
                <Link href={`/admin/${resource.key}/create`}>
                    <Button>
                        <span className="flex items-center gap-2">
                            <Plus size={16} />
                            إضافة {resource.labels.singular}
                        </span>
                    </Button>
                </Link>
            </div>

            <ResourceTable
                columns={columns}
                paginator={rows}
                searchPlaceholder={`ابحث في ${resource.labels.plural}…`}
                empty={`القائمة فارغة — ابدأ بإضافة ${resource.labels.singular}.`}
                actions={(row) => (
                    <div className="flex items-center justify-end gap-1">
                        <Link
                            href={`/admin/${resource.key}/${row.id}/edit`}
                            className="rounded-lg p-2 text-gray-500 transition hover:bg-gray-100 hover:text-secondary"
                            aria-label="تعديل"
                        >
                            <Pencil size={15} />
                        </Link>
                        {row._locked ? (
                            <span
                                className="cursor-not-allowed rounded-lg p-2 text-gray-300"
                                title="محمي من الحذف"
                                aria-label="محمي من الحذف"
                            >
                                <Lock size={15} />
                            </span>
                        ) : (
                            <button
                                onClick={() => remove(row)}
                                className="rounded-lg p-2 text-gray-400 transition hover:bg-danger/10 hover:text-danger"
                                aria-label="حذف"
                            >
                                <Trash2 size={15} />
                            </button>
                        )}
                    </div>
                )}
            />
        </AdminLayout>
    );
}
