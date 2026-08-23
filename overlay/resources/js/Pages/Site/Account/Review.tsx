import { useForm, usePage } from "@inertiajs/react";
import { Star } from "lucide-react";
import AccountLayout from "@/Layouts/AccountLayout";
import FormField, { inputClass } from "@/Components/site/FormField";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        title: "قيّم تجربتك",
        intro: "رأيك بينزل على الموقع باسمك بعد ما نراجعه. رأي واحد للحساب — تقدر تعدّله في أي وقت.",
        rating: "تقييمك",
        compound: "عن أي مشروع؟ (اختياري)",
        compoundAny: "الخدمة عمومًا",
        body: "رأيك",
        bodyHint: "٢٠ حرف على الأقل — اكتب اللي حصل معاك فعلًا، ده اللي بيفيد اللي بيقرا.",
        save: "ابعت رأيك",
        update: "حدّث رأيك",
        statusPending: "رأيك تحت المراجعة — هينزل على الموقع بعد ما نعتمده.",
        statusPublished: "رأيك منشور على الموقع ✅",
        statusRejected: "رأيك مانزلش. لو تحب تعدّله وتبعته تاني، اتفضّل.",
        note: "أي تعديل بيرجّع الرأي للمراجعة تاني.",
    },
    en: {
        title: "Rate your experience",
        intro: "Your review appears on the site under your name once we review it. One review per account — you can edit it any time.",
        rating: "Your rating",
        compound: "Which project? (optional)",
        compoundAny: "The service in general",
        body: "Your review",
        bodyHint: "At least 20 characters — write what actually happened; that is what helps the next reader.",
        save: "Send your review",
        update: "Update your review",
        statusPending: "Your review is under review — it appears once we approve it.",
        statusPublished: "Your review is live on the site ✅",
        statusRejected: "Your review was not published. Feel free to edit and resend it.",
        note: "Any edit sends the review back for review.",
    },
};

type Existing = {
    body: string;
    rating: number;
    compound_id: number | null;
    status: string;
    statusLabel: string;
};

type Option = { value: string; label: string };

export default function Review({ review, compounds }: { review: Existing | null; compounds: Option[] }) {
    const { locale, flash } = usePage<SharedProps>().props;
    const t = copy[locale === "en" ? "en" : "ar"];

    const { data, setData, post, processing, errors } = useForm({
        body: review?.body ?? "",
        rating: review?.rating ?? 5,
        compound_id: review?.compound_id ? String(review.compound_id) : "",
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/${locale}/account/review`, { preserveScroll: true });
    };

    // الحالة بتتقري من الرأي المحفوظ — الرسالة بتقول له هو فين بالظبط
    const statusText =
        review?.status === "published"
            ? t.statusPublished
            : review?.status === "rejected"
              ? t.statusRejected
              : review
                ? t.statusPending
                : null;

    return (
        <AccountLayout title={t.title}>
            <div className="rounded-2xl border border-border bg-white p-6 md:p-8">
                <p className="text-sm leading-[1.9] text-muted">{t.intro}</p>

                {flash?.success && (
                    <p className="mt-4 rounded-xl bg-success/10 px-4 py-3 text-sm font-bold text-success">
                        {flash.success}
                    </p>
                )}

                {statusText && !flash?.success && (
                    <p
                        className={`mt-4 rounded-xl px-4 py-3 text-sm font-bold ${
                            review?.status === "published"
                                ? "bg-success/10 text-success"
                                : "bg-amber-50 text-amber-700"
                        }`}
                    >
                        {statusText}
                    </p>
                )}

                <form onSubmit={submit} className="mt-6 flex flex-col gap-5">
                    <FormField label={t.rating} error={errors.rating}>
                        <div className="flex items-center gap-1.5">
                            {[1, 2, 3, 4, 5].map((n) => (
                                <button
                                    key={n}
                                    type="button"
                                    onClick={() => setData("rating", n)}
                                    aria-label={`${n} / 5`}
                                    aria-pressed={data.rating === n}
                                    className="rounded p-1 transition hover:scale-110"
                                >
                                    <Star
                                        size={26}
                                        className={n <= data.rating ? "fill-primary text-primary" : "text-gray-300"}
                                    />
                                </button>
                            ))}
                        </div>
                    </FormField>

                    {compounds.length > 0 && (
                        <FormField label={t.compound} error={errors.compound_id}>
                            <select
                                value={data.compound_id}
                                onChange={(e) => setData("compound_id", e.target.value)}
                                className={inputClass}
                            >
                                <option value="">{t.compoundAny}</option>
                                {compounds.map((c) => (
                                    <option key={c.value} value={c.value}>
                                        {c.label}
                                    </option>
                                ))}
                            </select>
                        </FormField>
                    )}

                    <FormField label={t.body} hint={t.bodyHint} error={errors.body}>
                        <textarea
                            rows={6}
                            value={data.body}
                            onChange={(e) => setData("body", e.target.value)}
                            className={inputClass}
                        />
                    </FormField>

                    <div className="flex flex-wrap items-center gap-4">
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-brand bg-primary px-8 py-3 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover disabled:opacity-60"
                        >
                            {review ? t.update : t.save}
                        </button>
                        {review && <span className="text-xs font-bold text-muted">{t.note}</span>}
                    </div>
                </form>
            </div>
        </AccountLayout>
    );
}
