import { Link, useForm } from "@inertiajs/react";
import { ArrowRight, Eye, EyeOff, ImagePlus } from "lucide-react";
import { useState } from "react";
import { Button, Card, Field, Input } from "@/Components/admin/ui";
import MediaPicker from "@/Components/admin/MediaPicker";
import { isVideo } from "@/lib/media";
import AdminLayout from "@/Layouts/AdminLayout";
import type { ResourceField, ResourceSchema } from "@/lib/types";

type Values = Record<string, string | number | boolean | null>;

/**
 * فورم عام لأي ريسورس — بيتبني من schema الكنترولر.
 * إضافة حقل جديد = سطر في fields() بتاع الكنترولر، من غير أي تعديل هنا.
 */
export default function ResourceForm({
    resource,
    item,
}: {
    resource: ResourceSchema;
    item: (Values & { id: number }) | null;
}) {
    const initial: Values = {};
    for (const f of resource.fields) {
        const v = item?.[f.name];
        initial[f.name] = f.type === "toggle" ? Boolean(v) : v === null || v === undefined ? "" : String(v);
    }

    const { data, setData, post, put, processing, errors } = useForm<Values>(initial);
    const [revealed, setRevealed] = useState<Record<string, boolean>>({});
    const [picking, setPicking] = useState<string | null>(null);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        if (item) put(`/admin/${resource.key}/${item.id}`);
        else post(`/admin/${resource.key}`);
    };

    const control = (f: ResourceField) => {
        const value = data[f.name];

        if (f.type === "toggle") {
            return (
                <button
                    type="button"
                    role="switch"
                    aria-checked={Boolean(value)}
                    onClick={() => setData(f.name, !value)}
                    className={`relative h-7 w-12 rounded-full transition ${value ? "bg-primary" : "bg-gray-300"}`}
                >
                    <span
                        className={`absolute top-1 h-5 w-5 rounded-full bg-white shadow transition-all ${
                            value ? "start-6" : "start-1"
                        }`}
                    />
                </button>
            );
        }

        if (f.type === "password") {
            const shown = revealed[f.name] ?? false;

            // جزيرة LTR كاملة: كده pe-11 و end-0 يبقوا على اليمين مع نص الباسورد الإنجليزي
            return (
                <div dir="ltr" className="relative">
                    <Input
                        type={shown ? "text" : "password"}
                        value={String(value ?? "")}
                        onChange={(e) => setData(f.name, e.target.value)}
                        autoComplete="new-password"
                        className="pe-11"
                    />
                    <button
                        type="button"
                        onClick={() => setRevealed((r) => ({ ...r, [f.name]: !shown }))}
                        className="absolute inset-y-0 end-0 flex items-center px-3 text-gray-400 transition hover:text-secondary"
                        aria-label={shown ? "إخفاء كلمة المرور" : "إظهار كلمة المرور"}
                    >
                        {shown ? <EyeOff size={16} /> : <Eye size={16} />}
                    </button>
                </div>
            );
        }

        if (f.type === "textarea") {
            return (
                <textarea
                    rows={4}
                    value={String(value ?? "")}
                    onChange={(e) => setData(f.name, e.target.value)}
                    className="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/25"
                />
            );
        }

        if (f.type === "select") {
            return (
                <select
                    value={String(value ?? "")}
                    onChange={(e) => setData(f.name, e.target.value)}
                    className="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/25"
                >
                    <option value="">— اختر —</option>
                    {(f.options ?? []).map((o) => (
                        <option key={o.value} value={o.value}>
                            {o.label}
                        </option>
                    ))}
                </select>
            );
        }

        if (f.type === "image") {
            const path = String(value ?? "");

            return (
                <div className="flex items-center gap-3">
                    {path ? (
                        isVideo(path) ? (
                            <video
                                src={path}
                                muted
                                className="h-12 w-12 shrink-0 rounded-lg border border-gray-200 object-cover"
                            />
                        ) : (
                            <img
                                src={path}
                                alt=""
                                className="h-12 w-12 shrink-0 rounded-lg border border-gray-200 object-cover"
                            />
                        )
                    ) : (
                        <span className="h-12 w-12 shrink-0 rounded-lg border border-dashed border-gray-300" />
                    )}

                    <Input
                        value={path}
                        onChange={(e) => setData(f.name, e.target.value)}
                        placeholder="/images/demo/..."
                        dir="ltr"
                    />

                    <button
                        type="button"
                        onClick={() => setPicking(f.name)}
                        className="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2.5 text-xs font-bold text-gray-700 transition hover:border-primary hover:text-secondary"
                    >
                        <ImagePlus size={15} />
                        المكتبة
                    </button>
                </div>
            );
        }

        if (f.type === "date") {
            return (
                <Input
                    type="date"
                    value={String(value ?? "")}
                    onChange={(e) => setData(f.name, e.target.value)}
                    dir="ltr"
                />
            );
        }

        return (
            <Input
                type={f.type === "number" ? "number" : "text"}
                value={String(value ?? "")}
                onChange={(e) => setData(f.name, e.target.value)}
                dir={f.type === "number" ? "ltr" : undefined}
            />
        );
    };

    const title = item ? `تعديل ${resource.labels.singular}` : `إضافة ${resource.labels.singular}`;

    return (
        <AdminLayout title={title}>
            <form onSubmit={submit}>
                <Card
                    title={title}
                    actions={
                        <div className="flex items-center gap-2">
                            <Link href={`/admin/${resource.key}`}>
                                <Button type="button" variant="ghost">
                                    <span className="flex items-center gap-2">
                                        <ArrowRight size={15} />
                                        رجوع
                                    </span>
                                </Button>
                            </Link>
                            <Button type="submit" disabled={processing}>
                                {processing ? "جارٍ الحفظ…" : "حفظ"}
                            </Button>
                        </div>
                    }
                >
                    <div className="grid gap-5 p-6 md:grid-cols-2">
                        {resource.fields.map((f) => (
                            <div key={f.name} className={f.type === "textarea" ? "md:col-span-2" : ""}>
                                <Field label={f.label} hint={f.hint} error={errors[f.name]}>
                                    {control(f)}
                                </Field>
                            </div>
                        ))}
                    </div>
                </Card>
            </form>

            <MediaPicker
                open={picking !== null}
                current={picking ? String(data[picking] ?? "") : undefined}
                onClose={() => setPicking(null)}
                onPick={(path) => picking && setData(picking, path)}
            />
        </AdminLayout>
    );
}
