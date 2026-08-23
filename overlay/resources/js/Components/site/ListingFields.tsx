import { usePage } from "@inertiajs/react";
import { ImagePlus, Trash2 } from "lucide-react";
import { useState } from "react";
import FormField, { inputClass } from "@/Components/site/FormField";
import type { Option, SharedProps } from "@/lib/types";

export interface ListingOptions {
    types: Option[];
    locations: Option[];
    finishing: Option[];
    maxImages: number;
}

/** setData بتاعة Inertia — الأنواع بتتقفل عند الاستدعاء مش هنا */
export type ListingSetter = (key: string, value: string | File[] | string[]) => void;

const copy = {
    ar: {
        unit: "بيانات الوحدة",
        details: "التفاصيل",
        media: "الصور",
        adTitle: "عنوان الإعلان",
        adTitleHint: "مثال: شقة ١٥٠م بحديقة في التجمع الخامس",
        purpose: "الغرض",
        sale: "بيع",
        rent: "إيجار",
        type: "نوع العقار",
        area: "المنطقة",
        choose: "اختر",
        price: "السعر المطلوب (جنيه)",
        priceHint: "اتركه فارغًا إذا كان «السعر عند الاستعلام»",
        size: "المساحة (م²)",
        beds: "غرف النوم",
        baths: "الحمامات",
        finishing: "التشطيب",
        floor: "الدور",
        floorHint: "مثال: الثالث · أرضي",
        delivery: "سنة التسليم",
        down: "المقدم (جنيه)",
        description: "وصف الوحدة",
        descriptionHint: "اكتب ما يميّز الوحدة: الإطلالة، الموقع، نظام السداد.",
        pick: "اختر صور الوحدة",
        pickHint: (n: number) => `حتى ${n} صور · JPG أو PNG أو WebP · ٥ ميجا للصورة`,
        current: "الصور الحالية",
        added: "الصور الجديدة",
        remove: "إزالة",
        first: "الرئيسية",
    },
    en: {
        unit: "Unit details",
        details: "Specifications",
        media: "Photos",
        adTitle: "Listing title",
        adTitleHint: "e.g. 150m² apartment with garden in New Cairo",
        purpose: "Purpose",
        sale: "Sale",
        rent: "Rent",
        type: "Property type",
        area: "Area",
        choose: "Choose",
        price: "Asking price (EGP)",
        priceHint: "Leave empty for “price on request”",
        size: "Size (m²)",
        beds: "Bedrooms",
        baths: "Bathrooms",
        finishing: "Finishing",
        floor: "Floor",
        floorHint: "e.g. Third · Ground",
        delivery: "Delivery year",
        down: "Down payment (EGP)",
        description: "Description",
        descriptionHint: "What makes the unit stand out: the view, the location, the payment plan.",
        pick: "Choose photos",
        pickHint: (n: number) => `Up to ${n} photos · JPG, PNG or WebP · 5MB each`,
        current: "Current photos",
        added: "New photos",
        remove: "Remove",
        first: "Main",
    },
};

export const listingSection = "rounded-3xl border border-gray-100 bg-surface p-6 md:p-7";
export const listingHeading = "mb-5 text-base font-extrabold text-secondary";

/**
 * حقول العرض — مشتركة بين «أضف عقارك» العام و«وحداتي» في حساب المعلن.
 * الاتنين بيكتبوا في نفس الجدول، فحقل يتضاف هنا بيوصلهم مع بعض.
 */
export default function ListingFields({
    data,
    setData,
    errors,
    options,
    existing = [],
}: {
    data: Record<string, string | File[] | string[]>;
    setData: ListingSetter;
    errors: Partial<Record<string, string>>;
    options: ListingOptions;
    /** صور محفوظة قبل كده — بتتعرض مع زرار شيل عند التعديل */
    existing?: string[];
}) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const [kept, setKept] = useState<string[]>(existing);
    const [previews, setPreviews] = useState<string[]>([]);

    const files = (data.images as File[]) ?? [];

    const pick = (list: FileList | null) => {
        const chosen = Array.from(list ?? []).slice(0, options.maxImages);

        setData("images", chosen);
        setPreviews(chosen.map((f) => URL.createObjectURL(f)));
    };

    const dropNew = (index: number) => {
        const rest = files.filter((_, i) => i !== index);

        setData("images", rest);
        setPreviews(rest.map((f) => URL.createObjectURL(f)));
    };

    const dropExisting = (path: string) => {
        const rest = kept.filter((p) => p !== path);

        setKept(rest);
        setData("keep", rest);
    };

    const val = (key: string) => String(data[key] ?? "");

    const text = (key: string, label: string, extra?: { hint?: string; required?: boolean; type?: string }) => (
        <FormField label={extra?.required ? `${label} *` : label} error={errors[key]} hint={extra?.hint}>
            <input
                type={extra?.type ?? "text"}
                inputMode={extra?.type === "number" ? "numeric" : undefined}
                dir={extra?.type === "number" ? "ltr" : "auto"}
                value={val(key)}
                onChange={(e) => setData(key, e.target.value)}
                className={inputClass}
            />
        </FormField>
    );

    const select = (key: string, label: string, items: Option[], required = false) => (
        <FormField label={required ? `${label} *` : label} error={errors[key]}>
            <select value={val(key)} onChange={(e) => setData(key, e.target.value)} className={inputClass}>
                <option value="">{t.choose}</option>
                {items.map((o) => (
                    <option key={o.value} value={o.value}>
                        {o.label}
                    </option>
                ))}
            </select>
        </FormField>
    );

    const thumb = (src: string, alt: string, onRemove: () => void, badge?: string) => (
        <li key={src} className="relative overflow-hidden rounded-xl border border-gray-100">
            <img src={src} alt="" className="h-24 w-full object-cover" />
            {badge && (
                <span className="absolute start-1.5 top-1.5 rounded-full bg-bg-dark/70 px-2 py-0.5 text-[10px] font-extrabold text-white">
                    {badge}
                </span>
            )}
            <button
                type="button"
                onClick={onRemove}
                aria-label={`${t.remove} — ${alt}`}
                className="absolute end-1.5 top-1.5 flex h-7 w-7 items-center justify-center rounded-full bg-bg-dark/70 text-white transition hover:bg-danger"
            >
                <Trash2 size={13} />
            </button>
        </li>
    );

    return (
        <>
            <fieldset className={listingSection}>
                <legend className={listingHeading}>{t.unit}</legend>
                <div className="grid gap-4">
                    {text("title", t.adTitle, { required: true, hint: t.adTitleHint })}

                    <div className="grid gap-4 md:grid-cols-3">
                        {select("purpose", t.purpose, [
                            { value: "sale", label: t.sale },
                            { value: "rent", label: t.rent },
                        ], true)}
                        {select("type", t.type, options.types, true)}
                        {select("location_id", t.area, options.locations)}
                    </div>

                    <div className="grid gap-4 md:grid-cols-3">
                        {text("price_amount", t.price, { type: "number", hint: t.priceHint })}
                        {text("down_payment", t.down, { type: "number" })}
                        {text("size", t.size, { type: "number" })}
                    </div>
                </div>
            </fieldset>

            <fieldset className={listingSection}>
                <legend className={listingHeading}>{t.details}</legend>
                <div className="grid gap-4">
                    <div className="grid gap-4 md:grid-cols-3">
                        {text("beds", t.beds, { type: "number" })}
                        {text("baths", t.baths, { type: "number" })}
                        {select("finishing", t.finishing, options.finishing)}
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        {text("floor", t.floor, { hint: t.floorHint })}
                        {text("delivery_year", t.delivery, { type: "number" })}
                    </div>

                    <FormField label={t.description} error={errors.description} hint={t.descriptionHint}>
                        <textarea
                            rows={5}
                            value={val("description")}
                            onChange={(e) => setData("description", e.target.value)}
                            className={inputClass}
                        />
                    </FormField>
                </div>
            </fieldset>

            <fieldset className={listingSection}>
                <legend className={listingHeading}>{t.media}</legend>

                {kept.length > 0 && (
                    <>
                        <p className="mb-2 text-[12px] font-extrabold text-muted">{t.current}</p>
                        <ul className="mb-5 grid grid-cols-3 gap-3 sm:grid-cols-4">
                            {kept.map((src, i) =>
                                thumb(src, src, () => dropExisting(src), i === 0 ? t.first : undefined),
                            )}
                        </ul>
                    </>
                )}

                <label className="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border border-dashed border-gray-300 bg-bg px-6 py-8 text-center transition hover:border-primary">
                    <ImagePlus size={26} className="text-primary" />
                    <span className="text-[13px] font-extrabold text-secondary">{t.pick}</span>
                    <span className="text-[11px] text-muted">{t.pickHint(options.maxImages)}</span>
                    <input type="file" accept="image/*" multiple onChange={(e) => pick(e.target.files)} className="hidden" />
                </label>

                {errors.images && <p className="mt-2 text-[12px] font-bold text-danger">{errors.images}</p>}

                {previews.length > 0 && (
                    <>
                        <p className="mt-5 mb-2 text-[12px] font-extrabold text-muted">{t.added}</p>
                        <ul className="grid grid-cols-3 gap-3 sm:grid-cols-4">
                            {previews.map((src, i) => thumb(src, String(i + 1), () => dropNew(i)))}
                        </ul>
                    </>
                )}
            </fieldset>
        </>
    );
}
