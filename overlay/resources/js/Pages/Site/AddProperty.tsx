import { useForm, usePage } from "@inertiajs/react";
import { CheckCircle2, ImagePlus, Send, ShieldCheck, Trash2 } from "lucide-react";
import { useState } from "react";
import FormField, { inputClass } from "@/Components/site/FormField";
import PageHero from "@/Components/site/PageHero";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Option, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "أضف عقارك",
        title: "أضف عقارك",
        desc: "اعرض وحدتك مجانًا. بنراجع البيانات وبننشرها بكود مرجعي، وبتوصلك طلبات المشترين على طول.",
        steps: [
            "املا بيانات الوحدة وارفع صورها",
            "فريقنا بيراجع البيانات خلال ٢٤ ساعة",
            "الإعلان بينزل بكود مرجعي وبتوصلك الطلبات",
        ],
        contact: "بيانات التواصل",
        unit: "بيانات الوحدة",
        details: "التفاصيل",
        media: "الصور",
        name: "الاسم",
        phone: "الموبايل",
        email: "الإيميل (اختياري)",
        adTitle: "عنوان الإعلان",
        adTitleHint: "مثال: شقة ١٥٠م بحديقة في التجمع الخامس",
        purpose: "الغرض",
        sale: "بيع",
        rent: "إيجار",
        type: "نوع العقار",
        area: "المنطقة",
        choose: "اختر",
        price: "السعر المطلوب (جنيه)",
        priceHint: "سيبه فاضي لو «السعر عند الاستعلام»",
        size: "المساحة (م²)",
        beds: "غرف النوم",
        baths: "الحمامات",
        finishing: "التشطيب",
        floor: "الدور",
        floorHint: "مثال: الثالث · أرضي",
        delivery: "سنة التسليم",
        down: "المقدم (جنيه)",
        description: "وصف الوحدة",
        descriptionHint: "اكتب اللي بيميّز الوحدة: الإطلالة، الموقع، نظام السداد.",
        pick: "اختر صور الوحدة",
        pickHint: (n: number) => `لحد ${n} صور · JPG أو PNG أو WebP · ٥ ميجا للصورة`,
        remove: "شيل",
        submit: "ابعت العقار للمراجعة",
        sending: "جارٍ الإرسال…",
        doneTitle: "وصلنا عقارك ✅",
        doneText: "الفريق بيراجع البيانات وبينشر الإعلان خلال ٢٤ ساعة. هنكلّمك لو احتجنا أي توضيح.",
        another: "أضف عقار تاني",
        note: "الإعلان مبيظهرش على الموقع قبل المراجعة — ده بيحمي الباحثين من الإعلانات الوهمية.",
        required: "الحقول المطلوبة عليها *",
    },
    en: {
        crumb: "Add your property",
        title: "Add your property",
        desc: "List your unit for free. We review the details, publish it with a reference number, and send you buyer requests.",
        steps: [
            "Fill in the unit details and upload photos",
            "Our team reviews them within 24 hours",
            "The listing goes live with a reference number",
        ],
        contact: "Contact details",
        unit: "Unit details",
        details: "Specifications",
        media: "Photos",
        name: "Name",
        phone: "Mobile",
        email: "Email (optional)",
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
        remove: "Remove",
        submit: "Send for review",
        sending: "Sending…",
        doneTitle: "Your property was received ✅",
        doneText: "Our team reviews the details and publishes the listing within 24 hours. We'll call if anything needs clarifying.",
        another: "Add another property",
        note: "Listings are not visible on the site before review — that is what keeps fake ads out.",
        required: "Required fields are marked *",
    },
};

interface Options {
    types: Option[];
    locations: Option[];
    finishing: Option[];
    maxImages: number;
}

const section = "rounded-3xl border border-gray-100 bg-surface p-6 md:p-7";
const heading = "mb-5 text-base font-extrabold text-secondary";

/**
 * فورم «أضف عقارك». بيعمل وحدة في انتظار المراجعة + طلب في صندوق الطلبات،
 * فالعرض بيدخل نفس دورة الاعتماد بتاعة أي وحدة بدل ما يتكتب تاني بالإيد.
 */
export default function AddProperty({ options }: { options: Options }) {
    const { locale, auth } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const [sent, setSent] = useState(false);
    const [previews, setPreviews] = useState<string[]>([]);

    const form = useForm({
        name: auth.user?.name ?? "",
        phone: "",
        email: auth.user?.email ?? "",
        title: "",
        purpose: "sale",
        type: "",
        location_id: "",
        price_amount: "",
        size: "",
        beds: "",
        baths: "",
        finishing: "",
        floor: "",
        delivery_year: "",
        down_payment: "",
        description: "",
        images: [] as File[],
        website: "",
    });

    const { data, setData, errors, processing } = form;

    const pick = (files: FileList | null) => {
        const chosen = Array.from(files ?? []).slice(0, options.maxImages);

        setData("images", chosen);
        setPreviews(chosen.map((f) => URL.createObjectURL(f)));
    };

    const drop = (index: number) => {
        const kept = data.images.filter((_, i) => i !== index);

        setData("images", kept);
        setPreviews(kept.map((f) => URL.createObjectURL(f)));
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        form.post(`/${locale}/add-property`, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                setPreviews([]);
                setSent(true);
            },
        });
    };

    const text = (key: keyof typeof data, label: string, extra?: { hint?: string; required?: boolean; type?: string }) => (
        <FormField label={extra?.required ? `${label} *` : label} error={errors[key]} hint={extra?.hint}>
            <input
                type={extra?.type ?? "text"}
                inputMode={extra?.type === "number" ? "numeric" : undefined}
                dir={extra?.type === "number" ? "ltr" : "auto"}
                value={String(data[key] ?? "")}
                onChange={(e) => setData(key, e.target.value as never)}
                className={inputClass}
            />
        </FormField>
    );

    const select = (key: keyof typeof data, label: string, items: Option[], required = false) => (
        <FormField label={required ? `${label} *` : label} error={errors[key]}>
            <select
                value={String(data[key] ?? "")}
                onChange={(e) => setData(key, e.target.value as never)}
                className={inputClass}
            >
                <option value="">{t.choose}</option>
                {items.map((o) => (
                    <option key={o.value} value={o.value}>
                        {o.label}
                    </option>
                ))}
            </select>
        </FormField>
    );

    return (
        <SiteLayout>
            <PageHero bg="/images/demo/bg-props.jpg" crumb={t.crumb} title={t.title} desc={t.desc}>
                <ol className="flex flex-wrap gap-x-6 gap-y-2 text-[13px] font-bold text-white/75">
                    {t.steps.map((step, i) => (
                        <li key={step} className="flex items-center gap-2">
                            <span className="flex h-6 w-6 items-center justify-center rounded-full bg-primary text-[11px] font-black text-primary-fg">
                                {i + 1}
                            </span>
                            {step}
                        </li>
                    ))}
                </ol>
            </PageHero>

            <section className="bg-bg px-4 py-12">
                <div className="mx-auto max-w-4xl">
                    {sent ? (
                        <div className="flex flex-col items-center rounded-3xl border border-gray-100 bg-surface px-6 py-16 text-center">
                            <CheckCircle2 size={40} className="text-success" />
                            <h2 className="mt-4 text-xl font-extrabold text-secondary">{t.doneTitle}</h2>
                            <p className="mt-2 max-w-md text-sm leading-7 text-muted">{t.doneText}</p>
                            <button
                                type="button"
                                onClick={() => setSent(false)}
                                className="mt-6 rounded-brand border border-gray-200 px-6 py-2.5 text-[13px] font-extrabold text-secondary transition hover:border-primary hover:text-primary"
                            >
                                {t.another}
                            </button>
                        </div>
                    ) : (
                        <form onSubmit={submit} className="flex flex-col gap-5">
                            <p className="text-[12px] font-bold text-muted">{t.required}</p>

                            <fieldset className={section}>
                                <legend className={heading}>{t.contact}</legend>
                                <div className="grid gap-4 md:grid-cols-3">
                                    {text("name", t.name, { required: true })}
                                    {text("phone", t.phone, { required: true })}
                                    {text("email", t.email, { type: "email" })}
                                </div>
                            </fieldset>

                            <fieldset className={section}>
                                <legend className={heading}>{t.unit}</legend>
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

                            <fieldset className={section}>
                                <legend className={heading}>{t.details}</legend>
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
                                            value={data.description}
                                            onChange={(e) => setData("description", e.target.value)}
                                            className={inputClass}
                                        />
                                    </FormField>
                                </div>
                            </fieldset>

                            <fieldset className={section}>
                                <legend className={heading}>{t.media}</legend>

                                <label className="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border border-dashed border-gray-300 bg-bg px-6 py-8 text-center transition hover:border-primary">
                                    <ImagePlus size={26} className="text-primary" />
                                    <span className="text-[13px] font-extrabold text-secondary">{t.pick}</span>
                                    <span className="text-[11px] text-muted">{t.pickHint(options.maxImages)}</span>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        multiple
                                        onChange={(e) => pick(e.target.files)}
                                        className="hidden"
                                    />
                                </label>

                                {errors.images && <p className="mt-2 text-[12px] font-bold text-danger">{errors.images}</p>}

                                {previews.length > 0 && (
                                    <ul className="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-4">
                                        {previews.map((src, i) => (
                                            <li key={src} className="group relative overflow-hidden rounded-xl border border-gray-100">
                                                <img src={src} alt="" className="h-24 w-full object-cover" />
                                                <button
                                                    type="button"
                                                    onClick={() => drop(i)}
                                                    aria-label={t.remove}
                                                    className="absolute end-1.5 top-1.5 flex h-7 w-7 items-center justify-center rounded-full bg-bg-dark/70 text-white transition hover:bg-danger"
                                                >
                                                    <Trash2 size={13} />
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </fieldset>

                            {/* مصيدة بوتس — مخفية عن الناس ومقروءة للسكربتات */}
                            <input
                                type="text"
                                tabIndex={-1}
                                autoComplete="off"
                                aria-hidden="true"
                                value={data.website}
                                onChange={(e) => setData("website", e.target.value)}
                                className="absolute -left-[9999px] h-0 w-0 opacity-0"
                            />

                            <div className="flex flex-wrap items-center gap-4">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="flex items-center gap-2 rounded-brand bg-primary px-8 py-3 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover disabled:opacity-60"
                                >
                                    <Send size={16} />
                                    {processing ? t.sending : t.submit}
                                </button>

                                <p className="flex items-center gap-2 text-[12px] font-bold text-muted">
                                    <ShieldCheck size={15} className="text-success" />
                                    {t.note}
                                </p>
                            </div>
                        </form>
                    )}
                </div>
            </section>
        </SiteLayout>
    );
}
