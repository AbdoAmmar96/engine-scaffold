import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, Building2, Inbox, Newspaper, Palette, Settings, Share2, ShieldCheck, Users } from "lucide-react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Card } from "@/Components/admin/ui";
import type { SharedProps } from "@/lib/types";

// كل لينك وصلاحياته — نفس التحقق اللي على الراوت، عشان محدش يشوف لينك هياخد عليه 403
type QuickLink = { href: string; label: string; desc: string; icon: typeof Users; perm: string[] };

const quickLinks: QuickLink[] = [
    { href: "/admin/settings/theme", label: "الهوية والألوان", desc: "غيّر ألوان وخطوط الموقع كله لحظيًا", icon: Palette, perm: ["manage settings"] },
    { href: "/admin/settings/general", label: "الإعدادات العامة", desc: "اسم المنصة والوصف التعريفي", icon: Settings, perm: ["manage settings"] },
    { href: "/admin/settings/contact", label: "بيانات التواصل", desc: "الواتساب والتليفون والإيميل", icon: Users, perm: ["manage settings"] },
    { href: "/admin/settings/social", label: "السوشيال ميديا", desc: "روابط منصات التواصل", icon: Share2, perm: ["manage settings"] },
    { href: "/admin/properties", label: "العقارات", desc: "أضف وحدة أو عدّل بياناتها", icon: Building2, perm: ["manage catalog", "manage listings"] },
    { href: "/admin/compounds", label: "الكمبوندات", desc: "المشاريع وأنظمة السداد", icon: Building2, perm: ["manage catalog", "manage projects"] },
    { href: "/admin/posts", label: "المدونة", desc: "اكتب مقال جديد أو عدّل مقال", icon: Newspaper, perm: ["manage content"] },
    { href: "/admin/leads", label: "الطلبات", desc: "الطلبات الجاية من فورم الموقع", icon: Inbox, perm: ["manage leads"] },
    { href: "/admin/users", label: "المستخدمون", desc: "الحسابات والأدوار والصلاحيات", icon: ShieldCheck, perm: ["manage users"] },
];

/**
 * خريطة بناء الإنجن. الكارت بيختفي لوحده أول ما كل المراحل تخلص،
 * فمحدش محتاج يفتكر يشيله — عدّل الحالة هنا وبس.
 */
type Phase = { name: string; status: "done" | "partial" | "todo"; note?: string };

const phases: Phase[] = [
    { name: "المرحلة 1 — التأسيس + Theme Engine", status: "done" },
    { name: "المرحلة 2 — Media Manager + المنيوهات + الأدوار", status: "done" },
    {
        name: "المرحلة 3 — Block Builder + أنماط الهيرو",
        status: "partial",
        note: "أنماط الهيرو خلصت · فاضل الـ Block Builder",
    },
    { name: "المرحلة 4 — العقارات والكمبوندات والمطوّرون والمناطق", status: "done" },
    { name: "المرحلة 5 — المستخدمون والطلبات والمدونة", status: "done" },
    { name: "المرحلة 6 — الأدوار والعزل ومساحة العميل", status: "done" },
];

const dotStyles: Record<Phase["status"], string> = {
    done: "bg-success",
    partial: "bg-amber-400",
    todo: "bg-gray-300",
};

type Stat = { label: string; value: number; href: string };
type Role = { key: string; label: string; note: string; scoped: boolean };

export default function Dashboard({ role, stats }: { role: Role; stats: Stat[] }) {
    const { auth } = usePage<SharedProps>().props;
    const can = auth.user?.can ?? [];

    const links = quickLinks.filter((l) => l.perm.some((perm) => can.includes(perm)));

    // حالة بناء الإنجن تخصّ مدير المنصّة — الوسيط والشركة مالهمش دعوة بيها
    const pending = can.includes("manage settings") ? phases.filter((p) => p.status !== "done").length : 0;

    return (
        <AdminLayout title="لوحة التحكم">
            {/* شريط الدور: بيوضّح للوسيط/الشركة إنهم شايفين بتاعهم بس */}
            <div className="mb-6 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-2xl border border-gray-200 bg-white px-5 py-4">
                <span className="flex items-center gap-2 text-sm font-extrabold text-gray-900">
                    <ShieldCheck size={17} className="text-primary" />
                    {role.label}
                </span>
                <span className="text-xs text-gray-500">{role.note}</span>
                {role.scoped && (
                    <span className="rounded-full bg-amber-100 px-3 py-1 text-[11px] font-extrabold text-amber-700">
                        بتشوف بياناتك بس
                    </span>
                )}
            </div>

            {stats.length > 0 && (
                <div className="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {stats.map((s) => (
                        <Link
                            key={s.href}
                            href={s.href}
                            className="flex flex-col rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-primary/60 hover:shadow-sm"
                        >
                            <span className="text-3xl font-black text-primary" dir="ltr">
                                {s.value}
                            </span>
                            <span className="mt-1 text-xs font-bold text-gray-500">{s.label}</span>
                        </Link>
                    ))}
                </div>
            )}

            <div className="grid gap-6 lg:grid-cols-3">
                <div className={pending ? "lg:col-span-2" : "lg:col-span-3"}>
                    {links.length > 0 && (
                    <Card title="روابط سريعة">
                        <div className="grid gap-4 sm:grid-cols-2">
                            {links.map(({ href, label, desc, icon: Icon }) => (
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
                    )}
                </div>

                {pending > 0 && (
                    <Card title="حالة البناء">
                        <ul className="flex flex-col gap-3">
                            {phases.map((p) => (
                                <li key={p.name} className="flex gap-3 text-sm">
                                    <span className={`mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full ${dotStyles[p.status]}`} />
                                    <span className="flex flex-col gap-1">
                                        <span className={p.status === "done" ? "font-bold text-gray-900" : "text-gray-500"}>
                                            {p.name}
                                        </span>
                                        {p.note && <span className="text-xs text-gray-400">{p.note}</span>}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </Card>
                )}
            </div>
        </AdminLayout>
    );
}
