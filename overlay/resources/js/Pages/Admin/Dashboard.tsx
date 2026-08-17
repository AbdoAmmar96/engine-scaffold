import { Link } from "@inertiajs/react";
import { ArrowLeft, Palette, Settings, Share2, Users } from "lucide-react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Card } from "@/Components/admin/ui";

const quickLinks = [
    { href: "/admin/settings/theme", label: "الهوية والألوان", desc: "غيّر ألوان وخطوط الموقع كله لحظيًا", icon: Palette },
    { href: "/admin/settings/general", label: "الإعدادات العامة", desc: "اسم المنصة والوصف التعريفي", icon: Settings },
    { href: "/admin/settings/contact", label: "بيانات التواصل", desc: "الواتساب والتليفون والإيميل", icon: Users },
    { href: "/admin/settings/social", label: "السوشيال ميديا", desc: "روابط منصات التواصل", icon: Share2 },
];

const phases = [
    { name: "المرحلة 1 — التأسيس + Theme Engine", done: true },
    { name: "المرحلة 2 — Media Manager + المنيوهات + الأدوار", done: false },
    { name: "المرحلة 3 — Block Builder + أنماط الهيرو", done: false },
    { name: "المرحلة 4 — العقارات والكمبوندات والمطوّرون والمناطق", done: false },
];

export default function Dashboard() {
    return (
        <AdminLayout title="لوحة التحكم">
            <div className="grid gap-6 lg:grid-cols-3">
                <div className="lg:col-span-2">
                    <Card title="روابط سريعة">
                        <div className="grid gap-4 sm:grid-cols-2">
                            {quickLinks.map(({ href, label, desc, icon: Icon }) => (
                                <Link
                                    key={href}
                                    href={href}
                                    className="group flex items-start gap-4 rounded-2xl border border-gray-200 p-5 transition hover:border-primary/60 hover:shadow-sm"
                                >
                                    <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                        <Icon size={20} />
                                    </span>
                                    <span>
                                        <span className="flex items-center gap-1 font-extrabold text-gray-900">
                                            {label}
                                            <ArrowLeft size={14} className="opacity-0 transition group-hover:opacity-100" />
                                        </span>
                                        <span className="mt-1 block text-xs text-gray-500">{desc}</span>
                                    </span>
                                </Link>
                            ))}
                        </div>
                    </Card>
                </div>

                <Card title="حالة البناء">
                    <ul className="flex flex-col gap-3">
                        {phases.map((p) => (
                            <li key={p.name} className="flex items-center gap-3 text-sm">
                                <span
                                    className={`h-2.5 w-2.5 shrink-0 rounded-full ${p.done ? "bg-success" : "bg-gray-300"}`}
                                />
                                <span className={p.done ? "font-bold text-gray-900" : "text-gray-500"}>{p.name}</span>
                            </li>
                        ))}
                    </ul>
                </Card>
            </div>
        </AdminLayout>
    );
}
