import { Link, useForm } from "@inertiajs/react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Button, Card, ColorField, Field, Input } from "@/Components/admin/ui";

/**
 * شاشة إعدادات ديناميكية واحدة بتخدم كل المجموعات:
 * - قيمة تبدأ بـ # → Color picker
 * - غير كده → Text input
 * الليبلات جاية من السيرفر (SettingsController::LABELS).
 */

interface Props {
    group: string;
    groupLabel: string;
    groups: { key: string; label: string }[];
    values: Record<string, string>;
    labels: Record<string, string>;
}

export default function Edit({ group, groupLabel, groups, values, labels }: Props) {
    const { data, setData, put, processing } = useForm<{ values: Record<string, string> }>({ values });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(`/admin/settings/${group}`, { preserveScroll: true });
    };

    const isColor = (v: string) => /^#[0-9a-fA-F]{3,8}$/.test(v ?? "");

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
                            <Field key={key} label={labels[key] ?? key}>
                                {isColor(value) ? (
                                    <ColorField value={value} onChange={(v) => setData("values", { ...data.values, [key]: v })} />
                                ) : (
                                    <Input
                                        value={value ?? ""}
                                        dir={/[\u0600-\u06FF]/.test(value ?? "") ? "rtl" : "ltr"}
                                        onChange={(e) => setData("values", { ...data.values, [key]: e.target.value })}
                                    />
                                )}
                            </Field>
                        ))}
                    </div>

                    {group === "theme" && (
                        <p className="mt-6 rounded-xl bg-primary/10 p-4 text-xs font-bold leading-relaxed text-secondary">
                            ⚡ أي تغيير هنا بينطبق على الموقع كله فور الحفظ — من غير أي build. افتح الموقع في تاب تاني
                            واعمل ريفريش بعد الحفظ وشوف بنفسك.
                        </p>
                    )}
                </Card>
            </form>
        </AdminLayout>
    );
}
