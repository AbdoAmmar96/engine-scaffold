import { useState } from "react";
import AdminLayout from "@/Layouts/AdminLayout";
import { Card } from "@/Components/admin/ui";

interface Slice {
    label: string;
    value: number;
    tone?: string;
}

interface TopProperty {
    id: number;
    label: string;
    ref: string;
    views: number;
    leads: number;
}

interface AdRow {
    id: number;
    label: string;
    position: string;
    state: { label: string; tone: string };
    impressions: number;
    clicks: number;
    ctr: number;
}

const tones: Record<string, string> = {
    success: "bg-success",
    warn: "bg-amber-500",
    danger: "bg-danger",
    primary: "bg-primary",
    muted: "bg-gray-400",
};

const num = (n: number) => n.toLocaleString("en-US");

/**
 * تقارير الأداء.
 *
 * كل رسمة هنا سلسلة واحدة، فاللون واحد والطول هو اللي بيحمل المعنى —
 * ألوان مختلفة لأعمدة نفس السلسلة بتوحي بتصنيف مش موجود. الاستثناء
 * الوحيد قمع الطلبات: هناك اللون حالة (جديد/مؤهّل/مغلق) ومكتوب جنبه
 * اسمها، مش لون لوحده.
 */
export default function Reports({
    days,
    totals,
    leadsBySource,
    leadsByStatus,
    topAreas,
    topProperties,
    ads,
    daily,
}: {
    days: number;
    totals: Slice[];
    leadsBySource: Slice[];
    leadsByStatus: Slice[];
    topAreas: Slice[];
    topProperties: TopProperty[];
    ads: AdRow[];
    daily: { day: string; value: number }[];
}) {
    const [hover, setHover] = useState<number | null>(null);

    /** أعمدة أفقية — سلسلة واحدة بلون واحد، الطول هو القياس */
    const bars = (rows: Slice[], statusTone = false) => {
        const max = Math.max(1, ...rows.map((r) => r.value));

        if (rows.length === 0) {
            return <p className="py-6 text-center text-sm text-gray-400">لسه مفيش بيانات كفاية</p>;
        }

        return (
            <ul className="flex flex-col gap-3">
                {rows.map((r) => (
                    <li key={r.label} className="grid grid-cols-[6.5rem_1fr_2.5rem] items-center gap-2 sm:grid-cols-[9rem_1fr_3rem] sm:gap-3">
                        <span className="truncate text-[12px] font-bold text-gray-600" title={r.label}>
                            {r.label}
                        </span>

                        <span className="h-2.5 overflow-hidden rounded-full bg-gray-100">
                            <span
                                className={`block h-full rounded-full ${statusTone ? tones[r.tone ?? "muted"] : "bg-primary"}`}
                                style={{ width: `${Math.max(2, (r.value / max) * 100)}%` }}
                            />
                        </span>

                        <span dir="ltr" className="text-end text-[12px] font-extrabold text-gray-900">
                            {num(r.value)}
                        </span>
                    </li>
                ))}
            </ul>
        );
    };

    /* ---------- خط الطلبات اليومي ---------- */
    const maxDaily = Math.max(1, ...daily.map((d) => d.value));
    const stepX = daily.length > 1 ? 100 / (daily.length - 1) : 0;
    const points = daily.map((d, i) => [i * stepX, 40 - (d.value / maxDaily) * 34] as const);
    const path = points.map(([x, y], i) => `${i === 0 ? "M" : "L"}${x.toFixed(2)},${y.toFixed(2)}`).join(" ");
    const area = `${path} L100,40 L0,40 Z`;

    return (
        <AdminLayout title="التقارير">
            <div className="flex flex-col gap-6">
                {/* الأرقام الكبيرة — مش رسمة، رقم واحد بيتقرا فورًا */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    {totals.map((t) => (
                        <div key={t.label} className="rounded-2xl border border-gray-200 bg-white p-5">
                            <p dir="ltr" className="text-3xl font-black text-gray-900">
                                {num(t.value)}
                            </p>
                            <p className="mt-1 text-[12px] font-bold text-gray-500">{t.label}</p>
                        </div>
                    ))}
                </div>

                <Card title={`الطلبات — آخر ${days} يوم`}>
                    {daily.every((d) => d.value === 0) ? (
                        <p className="py-6 text-center text-sm text-gray-400">مفيش طلبات في الفترة دي</p>
                    ) : (
                        <div className="relative">
                            <svg viewBox="0 0 100 40" preserveAspectRatio="none" className="h-40 w-full" role="img"
                                aria-label={`عدد الطلبات في آخر ${days} يوم`}>
                                <path d={area} className="fill-primary/10" />
                                <path
                                    d={path}
                                    className="stroke-primary"
                                    fill="none"
                                    strokeWidth="0.6"
                                    vectorEffect="non-scaling-stroke"
                                    strokeLinejoin="round"
                                    strokeLinecap="round"
                                />
                                {hover !== null && (
                                    <circle
                                        cx={points[hover][0]}
                                        cy={points[hover][1]}
                                        r="1.2"
                                        className="fill-primary stroke-white"
                                        strokeWidth="0.6"
                                        vectorEffect="non-scaling-stroke"
                                    />
                                )}
                            </svg>

                            {/* مناطق التمرير فوق الرسمة — أعرض من النقطة عشان تتمسك بسهولة */}
                            <div className="absolute inset-0 flex">
                                {daily.map((d, i) => (
                                    <button
                                        key={d.day}
                                        type="button"
                                        onMouseEnter={() => setHover(i)}
                                        onFocus={() => setHover(i)}
                                        onMouseLeave={() => setHover(null)}
                                        onBlur={() => setHover(null)}
                                        aria-label={`${d.day}: ${d.value}`}
                                        className="h-full flex-1"
                                    />
                                ))}
                            </div>

                            <p className="mt-2 text-center text-[12px] font-bold text-gray-500">
                                {hover !== null ? (
                                    <span dir="ltr">
                                        {daily[hover].day} — {num(daily[hover].value)}
                                    </span>
                                ) : (
                                    <span dir="ltr">
                                        {daily[0]?.day} → {daily[daily.length - 1]?.day}
                                    </span>
                                )}
                            </p>
                        </div>
                    )}
                </Card>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card title="مصادر الطلبات">{bars(leadsBySource)}</Card>
                    <Card title="مراحل المتابعة">{bars(leadsByStatus, true)}</Card>
                    <Card title="أكتر المناطق طلبًا">{bars(topAreas)}</Card>

                    <Card title="أكتر الوحدات مشاهدة">
                        {topProperties.length === 0 ? (
                            <p className="py-6 text-center text-sm text-gray-400">لسه مفيش وحدات منشورة</p>
                        ) : (
                            <div className="overflow-x-auto">
                            <table className="w-full text-[12px]">
                                <thead>
                                    <tr className="text-gray-500">
                                        <th className="pb-2 text-start font-bold">الوحدة</th>
                                        <th className="pb-2 text-end font-bold">مشاهدات</th>
                                        <th className="pb-2 text-end font-bold">طلبات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {topProperties.map((p) => (
                                        <tr key={p.id} className="border-t border-gray-100">
                                            <td className="max-w-[16rem] truncate py-2 font-bold text-gray-800" title={p.label}>
                                                {p.label}
                                                {p.ref && <span dir="ltr" className="ms-2 text-gray-400">{p.ref}</span>}
                                            </td>
                                            <td dir="ltr" className="py-2 text-end font-extrabold text-gray-900">{num(p.views)}</td>
                                            <td dir="ltr" className="py-2 text-end font-extrabold text-gray-900">{num(p.leads)}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            </div>
                        )}
                    </Card>
                </div>

                <Card title="أداء المساحات الإعلانية">
                    {ads.length === 0 ? (
                        <p className="py-6 text-center text-sm text-gray-400">مفيش مساحات إعلانية لسه</p>
                    ) : (
                        <div className="overflow-x-auto">
                        <table className="w-full text-[12px]">
                            <thead>
                                <tr className="text-gray-500">
                                    <th className="pb-2 text-start font-bold">الإعلان على</th>
                                    <th className="pb-2 text-start font-bold">الموضع</th>
                                    <th className="pb-2 text-start font-bold">الحالة</th>
                                    <th className="pb-2 text-end font-bold">ظهور</th>
                                    <th className="pb-2 text-end font-bold">ضغطات</th>
                                    <th className="pb-2 text-end font-bold">نسبة الضغط</th>
                                </tr>
                            </thead>
                            <tbody>
                                {ads.map((a) => (
                                    <tr key={a.id} className="border-t border-gray-100">
                                        <td className="max-w-[14rem] truncate py-2 font-bold text-gray-800" title={a.label}>
                                            {a.label}
                                        </td>
                                        <td className="py-2 text-gray-600">{a.position}</td>
                                        <td className="py-2">
                                            <span className="flex w-fit items-center gap-1.5 text-gray-600">
                                                <span className={`h-2 w-2 rounded-full ${tones[a.state.tone] ?? tones.muted}`} />
                                                {a.state.label}
                                            </span>
                                        </td>
                                        <td dir="ltr" className="py-2 text-end font-extrabold text-gray-900">{num(a.impressions)}</td>
                                        <td dir="ltr" className="py-2 text-end font-extrabold text-gray-900">{num(a.clicks)}</td>
                                        <td dir="ltr" className="py-2 text-end font-extrabold text-gray-900">{a.ctr}%</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        </div>
                    )}
                </Card>
            </div>
        </AdminLayout>
    );
}
