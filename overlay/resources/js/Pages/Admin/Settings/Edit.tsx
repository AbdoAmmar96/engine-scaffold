import { Link, useForm } from "@inertiajs/react";
import { ImagePlus } from "lucide-react";
import { useState } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Button, Card, ColorField, Field, Input } from "@/Components/admin/ui";
import MediaPicker from "@/Components/admin/MediaPicker";
import { isVideo } from "@/lib/media";

/**
 * شاشة إعدادات ديناميكية واحدة بتخدم كل المجموعات.
 * نوع كل حقل جاي من السيرفر (SettingsController::TYPES) مش متخمّن من القيمة،
 * عشان لون اتمسح ميتحولش لحقل نص ويضيع الـ color picker.
 */

type FieldType = "color" | "media" | "select" | "textarea" | "toggle" | "text";

interface Props {
    group: string;
    groupLabel: string;
    groups: { key: string; label: string }[];
    values: Record<string, string>;
    labels: Record<string, string>;
    types: Record<string, FieldType>;
    options: Record<string, { value: string; label: string }[]>;
    hints: Record<string, string>;
}

export default function Edit({ group, groupLabel, groups, values, labels, types, options, hints }: Props) {
    const { data, setData, put, processing } = useForm<{ values: Record<string, string> }>({ values });
    const [picking, setPicking] = useState<string | null>(null);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/settings/${group}`, { preserveScroll: true });
    };

    const set = (key: string, v: string) => setData("values", { ...data.values, [key]: v });

    const control = (key: string, value: string) => {
        const type: FieldType = types[key] ?? "text";

        if (type === "color") return <ColorField value={value ?? ""} onChange={(v) => set(key, v)} />;

        // القيمة بتتخزّن نص "0"/"1" زي باقي الإعدادات — الجدول كله نصوص
        if (type === "toggle") {
            const on = value === "1";

            return (
                <button
                    type="button"
                    role="switch"
                    aria-checked={on}
                    onClick={() => set(key, on ? "0" : "1")}
                    className={`relative h-7 w-12 rounded-full transition ${on ? "bg-primary" : "bg-gray-300"}`}
                >
                    <span
                        className={`absolute top-1 h-5 w-5 rounded-full bg-white shadow transition-all ${
                            on ? "start-6" : "start-1"
                        }`}
                    />
                </button>
            );
        }

        if (type === "select") {
            return (
                <select
                    value={value ?? ""}
                    onChange={(e) => set(key, e.target.value)}
                    className="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/25"
                >
                    <option value="">— اختر —</option>
                    {(options[key] ?? []).map((o) => (
                        <option key={o.value} value={o.value}>
                            {o.label}
                        </option>
                    ))}
                </select>
            );
        }

        if (type === "textarea") {
            return (
                <textarea
                    rows={3}
                    value={value ?? ""}
                    onChange={(e) => set(key, e.target.value)}
                    className="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/25"
                />
            );
        }

        if (type === "media") {
            const local = value?.startsWith("/");

            return (
                <div className="flex items-center gap-3">
                    {local ? (
                        isVideo(value) ? (
                            <video src={value} muted className="h-12 w-12 shrink-0 rounded-lg border border-gray-200 object-cover" />
                        ) : (
                            <img src={value} alt="" className="h-12 w-12 shrink-0 rounded-lg border border-gray-200 object-cover" />
                        )
                    ) : (
                        <span className="h-12 w-12 shrink-0 rounded-lg border border-dashed border-gray-300" />
                    )}

                    <Input value={value ?? ""} dir="ltr" onChange={(e) => set(key, e.target.value)} />

                    <button
                        type="button"
                        onClick={() => setPicking(key)}
                        className="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-2.5 text-xs font-bold text-gray-700 transition hover:border-primary hover:text-secondary"
                    >
                        <ImagePlus size={15} />
                        المكتبة
                    </button>
                </div>
            );
        }

        return (
            <Input
                value={value ?? ""}
                dir={/[؀-ۿ]/.test(value ?? "") ? "rtl" : "ltr"}
                onChange={(e) => set(key, e.target.value)}
            />
        );
    };

    return (
        <AdminLayout title={`الإعدادات — ${groupLabel}`}>
            {/* تبويبات المجموعات */}
            <div className="mb-6 flex flex-wrap gap-2">
                {groups.map((g) => (
                    <Link
                        key={g.key}
                        href={`/admin/settings/${g.key}`}
                        className={`rounded-xl px-4 py-2 text-sm font-bold transition ${
                            g.key === group ? "bg-primary text-primary-fg" : "bg-white text-gray-600 hover:bg-gray-50"
                        }`}
                    >
                        {g.label}
                    </Link>
                ))}
            </div>

            <form onSubmit={submit}>
                <Card
                    title={groupLabel}
                    actions={
                        <Button type="submit" disabled={processing}>
                            {processing ? "جارٍ الحفظ…" : "حفظ التغييرات"}
                        </Button>
                    }
                >
                    <div className="grid gap-5 md:grid-cols-2">
                        {Object.entries(data.values).map(([key, value]) => (
                            <div key={key} className={types[key] === "textarea" ? "md:col-span-2" : ""}>
                                <Field label={labels[key] ?? key} hint={hints[key]}>
                                    {control(key, value)}
                                </Field>
                            </div>
                        ))}
                    </div>

                    {group === "theme" && (
                        <p className="mt-6 rounded-xl bg-primary/10 p-4 text-xs font-bold leading-relaxed text-secondary">
                            ⚡ أي تغيير هنا يُطبَّق على الموقع كله فور الحفظ — دون أي build. افتح الموقع في تبويب آخر
                            وحدِّث الصفحة بعد الحفظ لترى بنفسك.
                        </p>
                    )}
                </Card>
            </form>

            <MediaPicker
                open={picking !== null}
                current={picking ? data.values[picking] : undefined}
                onClose={() => setPicking(null)}
                onPick={(path) => picking && set(picking, path)}
            />
        </AdminLayout>
    );
}
