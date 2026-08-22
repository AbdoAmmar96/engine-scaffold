import { Link, useForm, usePage } from "@inertiajs/react";
import { ArrowLeft, Save } from "lucide-react";
import AccountLayout from "@/Layouts/AccountLayout";
import ListingFields, { type ListingOptions } from "@/Components/site/ListingFields";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: {
        create: "إضافة وحدة",
        edit: "تعديل الوحدة",
        back: "رجوع لوحداتي",
        submit: "احفظ وابعت للمراجعة",
        saving: "جارٍ الحفظ…",
        required: "الحقول المطلوبة عليها *",
        note: "أي تعديل بيرجّع الوحدة للمراجعة قبل ما تنزل تاني على الموقع.",
    },
    en: {
        create: "Add a listing",
        edit: "Edit listing",
        back: "Back to my listings",
        submit: "Save and send for review",
        saving: "Saving…",
        required: "Required fields are marked *",
        note: "Any edit sends the listing back to review before it goes live again.",
    },
};

interface Listing {
    id: number;
    title: string;
    purpose: string;
    type: string;
    location_id: string;
    price_amount: string;
    size: string;
    beds: string;
    baths: string;
    finishing: string;
    floor: string;
    delivery_year: string;
    down_payment: string;
    description: string;
    gallery: string[];
}

/** إضافة/تعديل وحدة من حساب المعلن — نفس حقول «أضف عقارك» بالظبط */
export default function PropertyForm({
    property,
    options,
}: {
    property: Listing | null;
    options: ListingOptions;
}) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const form = useForm({
        title: property?.title ?? "",
        purpose: property?.purpose ?? "sale",
        type: property?.type ?? "",
        location_id: property?.location_id ?? "",
        price_amount: property?.price_amount ?? "",
        size: property?.size ?? "",
        beds: property?.beds ?? "",
        baths: property?.baths ?? "",
        finishing: property?.finishing ?? "",
        floor: property?.floor ?? "",
        delivery_year: property?.delivery_year ?? "",
        down_payment: property?.down_payment ?? "",
        description: property?.description ?? "",
        images: [] as File[],
        keep: property?.gallery ?? [],
    });

    const { data, setData, errors, processing } = form;

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        const base = `/${locale}/account/my-properties`;

        // الملفات محتاجة multipart، و PUT مبيمشيش بيه — فالتعديل بيتبعت
        // POST ومعاه _method زي ما لارافيل بتتوقع
        if (property) {
            form.transform((d) => ({ ...d, _method: "put" }));
            form.post(`${base}/${property.id}`, { forceFormData: true });

            return;
        }

        form.post(base, { forceFormData: true });
    };

    return (
        <AccountLayout title={property ? t.edit : t.create}>
            <Link
                href={`/${locale}/account/my-properties`}
                className="flex w-fit items-center gap-2 text-[13px] font-extrabold text-muted transition hover:text-primary"
            >
                <ArrowLeft size={14} className="rtl:rotate-180" />
                {t.back}
            </Link>

            <form onSubmit={submit} className="flex flex-col gap-5">
                <p className="text-[12px] font-bold text-muted">{t.required}</p>

                <ListingFields
                    data={data as never}
                    setData={(k, v) => setData(k as never, v as never)}
                    errors={errors}
                    options={options}
                    existing={property?.gallery ?? []}
                />

                <div className="flex flex-wrap items-center gap-4">
                    <button
                        type="submit"
                        disabled={processing}
                        className="flex items-center gap-2 rounded-brand bg-primary px-8 py-3 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover disabled:opacity-60"
                    >
                        <Save size={16} />
                        {processing ? t.saving : t.submit}
                    </button>

                    <p className="text-[12px] font-bold text-muted">{t.note}</p>
                </div>
            </form>
        </AccountLayout>
    );
}
