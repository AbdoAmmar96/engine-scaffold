import { Link, router, usePage } from "@inertiajs/react";
import { Building2, Eye, Heart, Inbox, Pencil, Plus, Trash2, EyeOff } from "lucide-react";
import AccountLayout from "@/Layouts/AccountLayout";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        title: "وحداتي",
        add: "أضف وحدة",
        empty: "لسه مضفتش أي وحدة",
        emptyText: "ابدأ بوحدة واحدة — بنراجعها وبتنزل بكود مرجعي، وبعدها بتتابع مشاهداتها وطلباتها من هنا.",
        total: "وحدة",
        published: "منشورة",
        pending: "تحت المراجعة",
        views: "مشاهدة",
        requests: "طلب",
        edit: "تعديل",
        hide: "إخفاء",
        show: "إظهار",
        remove: "حذف",
        view: "افتح الصفحة",
        hidden: "مخفية بمعرفتك",
        confirm: "تحذف الوحدة دي نهائيًا؟",
        stats: { views: "مشاهدات", saves: "محفوظة", requests: "طلبات" },
        rejected: "سبب الرفض:",
    },
    en: {
        title: "My listings",
        add: "Add a listing",
        empty: "You haven't added a listing yet",
        emptyText: "Start with one — we review it, publish it with a reference number, and you track its views and requests here.",
        total: "listings",
        published: "live",
        pending: "in review",
        views: "views",
        requests: "requests",
        edit: "Edit",
        hide: "Hide",
        show: "Show",
        remove: "Delete",
        view: "Open page",
        hidden: "Hidden by you",
        confirm: "Delete this listing permanently?",
        stats: { views: "Views", saves: "Saves", requests: "Requests" },
        rejected: "Rejection reason:",
    },
};

interface Listing {
    id: number;
    title: string;
    slug: string;
    image: string;
    area: string;
    price: string;
    ref: string;
    status: { label: string; tone: string };
    rejection: string;
    live: boolean;
    hidden: boolean;
    views: number;
    saves: number;
    requests: number;
}

const tones: Record<string, string> = {
    success: "bg-success/10 text-success",
    warn: "bg-amber-100 text-amber-700",
    danger: "bg-danger/10 text-danger",
    primary: "bg-primary/10 text-primary",
    muted: "bg-gray-100 text-muted",
};

/** «وحداتي» — المعلن بيتابع عروضه وأداءها من غير ما يدخل لوحة التحكم */
export default function Properties({
    properties,
    summary,
}: {
    properties: Listing[];
    summary: { total: number; published: number; pending: number; views: number; leads: number };
}) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const addButton = (
        <Link
            href={`/${locale}/account/my-properties/create`}
            className="flex items-center gap-2 rounded-brand bg-primary px-5 py-2.5 text-[13px] font-extrabold text-primary-fg transition hover:bg-primary-hover"
        >
            <Plus size={15} />
            {t.add}
        </Link>
    );

    const stat = (value: number, label: string, Icon: typeof Eye) => (
        <span className="flex items-center gap-1.5 text-[12px] font-bold text-muted">
            <Icon size={13} />
            <span dir="auto" className="text-secondary">{value}</span>
            {label}
        </span>
    );

    return (
        <AccountLayout title={t.title}>
            <div className="flex flex-wrap items-center justify-between gap-4">
                <div className="flex flex-wrap gap-x-5 gap-y-2 text-[13px] font-bold text-muted">
                    <span>
                        <span dir="auto" className="text-lg font-black text-secondary">{summary.total}</span> {t.total}
                    </span>
                    <span>
                        <span dir="auto" className="text-lg font-black text-success">{summary.published}</span> {t.published}
                    </span>
                    <span>
                        <span dir="auto" className="text-lg font-black text-amber-600">{summary.pending}</span> {t.pending}
                    </span>
                    <span>
                        <span dir="auto" className="text-lg font-black text-secondary">{summary.views}</span> {t.views}
                    </span>
                    <span>
                        <span dir="auto" className="text-lg font-black text-secondary">{summary.leads}</span> {t.requests}
                    </span>
                </div>

                {addButton}
            </div>

            {properties.length === 0 ? (
                <div className="flex flex-col items-center rounded-3xl border border-gray-100 bg-bg px-6 py-16 text-center">
                    <span className="flex h-14 w-14 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <Building2 size={26} />
                    </span>
                    <h3 className="mt-4 text-lg font-extrabold text-secondary">{t.empty}</h3>
                    <p className="mt-2 max-w-md text-sm leading-7 text-muted">{t.emptyText}</p>
                    <div className="mt-6">{addButton}</div>
                </div>
            ) : (
                <ul className="flex flex-col gap-4">
                    {properties.map((p) => (
                        <li key={p.id} className="flex flex-col gap-4 rounded-3xl border border-gray-100 bg-bg p-4 sm:flex-row">
                            <img src={p.image} alt="" className="h-32 w-full shrink-0 rounded-2xl object-cover sm:w-44" />

                            <div className="flex min-w-0 flex-1 flex-col gap-2">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className={`rounded-full px-2.5 py-1 text-[11px] font-extrabold ${tones[p.status.tone] ?? tones.muted}`}>
                                        {p.status.label}
                                    </span>
                                    {p.hidden && (
                                        <span className="rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-extrabold text-muted">
                                            {t.hidden}
                                        </span>
                                    )}
                                    {p.ref && <span dir="ltr" className="text-[11px] font-bold text-muted">{p.ref}</span>}
                                </div>

                                <h3 className="truncate text-[15px] font-extrabold text-secondary">{p.title}</h3>

                                <p className="text-[13px] font-bold text-muted">
                                    {p.area && <span>{p.area} · </span>}
                                    <span dir="auto" className="text-primary">{p.price}</span>
                                </p>

                                {p.rejection && (
                                    <p className="rounded-xl bg-danger/5 px-3 py-2 text-[12px] font-bold text-danger">
                                        {t.rejected} {p.rejection}
                                    </p>
                                )}

                                <div className="flex flex-wrap items-center gap-x-4 gap-y-1">
                                    {stat(p.views, t.stats.views, Eye)}
                                    {stat(p.saves, t.stats.saves, Heart)}
                                    {stat(p.requests, t.stats.requests, Inbox)}
                                </div>
                            </div>

                            <div className="flex shrink-0 flex-wrap items-start gap-2">
                                {p.live && p.slug && (
                                    <a
                                        href={`/${locale}/properties/${p.slug}`}
                                        className="rounded-brand border border-gray-200 px-3 py-2 text-[12px] font-extrabold text-secondary transition hover:border-primary hover:text-primary"
                                    >
                                        {t.view}
                                    </a>
                                )}

                                <Link
                                    href={`/${locale}/account/my-properties/${p.id}/edit`}
                                    className="flex items-center gap-1.5 rounded-brand border border-gray-200 px-3 py-2 text-[12px] font-extrabold text-secondary transition hover:border-primary hover:text-primary"
                                >
                                    <Pencil size={13} />
                                    {t.edit}
                                </Link>

                                <button
                                    type="button"
                                    onClick={() => router.post(`/${locale}/account/my-properties/${p.id}/toggle`, {}, { preserveScroll: true })}
                                    className="flex items-center gap-1.5 rounded-brand border border-gray-200 px-3 py-2 text-[12px] font-extrabold text-secondary transition hover:border-primary hover:text-primary"
                                >
                                    {p.hidden ? <Eye size={13} /> : <EyeOff size={13} />}
                                    {p.hidden ? t.show : t.hide}
                                </button>

                                <button
                                    type="button"
                                    onClick={() => {
                                        if (confirm(t.confirm)) {
                                            router.delete(`/${locale}/account/my-properties/${p.id}`);
                                        }
                                    }}
                                    className="flex items-center gap-1.5 rounded-brand border border-gray-200 px-3 py-2 text-[12px] font-extrabold text-muted transition hover:border-danger hover:text-danger"
                                >
                                    <Trash2 size={13} />
                                    {t.remove}
                                </button>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </AccountLayout>
    );
}
