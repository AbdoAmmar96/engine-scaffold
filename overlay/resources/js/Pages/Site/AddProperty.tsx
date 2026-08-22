import { useForm, usePage } from "@inertiajs/react";
import { CheckCircle2, Send, ShieldCheck } from "lucide-react";
import { useState } from "react";
import FormField, { inputClass } from "@/Components/site/FormField";
import ListingFields, { listingHeading, listingSection, type ListingOptions } from "@/Components/site/ListingFields";
import PageHero from "@/Components/site/PageHero";
import SiteLayout from "@/Layouts/SiteLayout";
import type { SharedProps } from "@/lib/types";

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
        name: "الاسم",
        phone: "الموبايل",
        email: "الإيميل (اختياري)",
        submit: "ابعت العقار للمراجعة",
        sending: "جارٍ الإرسال…",
        doneTitle: "وصلنا عقارك ✅",
        doneText: "الفريق بيراجع البيانات وبينشر الإعلان خلال ٢٤ ساعة. هنكلّمك لو احتجنا أي توضيح.",
        another: "أضف عقار تاني",
        note: "الإعلان مبيظهرش على الموقع قبل المراجعة — ده بيحمي الباحثين من الإعلانات الوهمية.",
        required: "الحقول المطلوبة عليها *",
        mine: "تابع وحداتك من «وحداتي»",
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
        name: "Name",
        phone: "Mobile",
        email: "Email (optional)",
        submit: "Send for review",
        sending: "Sending…",
        doneTitle: "Your property was received ✅",
        doneText: "Our team reviews the details and publishes the listing within 24 hours. We'll call if anything needs clarifying.",
        another: "Add another property",
        note: "Listings are not visible on the site before review — that is what keeps fake ads out.",
        required: "Required fields are marked *",
        mine: "Track them under “My listings”",
    },
};

/**
 * فورم «أضف عقارك». بيعمل وحدة في انتظار المراجعة + طلب في صندوق الطلبات،
 * فالعرض بيدخل نفس دورة الاعتماد بتاعة أي وحدة بدل ما يتكتب تاني بالإيد.
 */
export default function AddProperty({ options }: { options: ListingOptions }) {
    const { locale, auth } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const [sent, setSent] = useState(false);

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

    const submit = (e: React.FormEvent) => {
        e.preventDefault();

        form.post(`/${locale}/add-property`, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                setSent(true);
            },
        });
    };

    const contact = (key: "name" | "phone" | "email", label: string, required = false, type = "text") => (
        <FormField label={required ? `${label} *` : label} error={errors[key]}>
            <input
                type={type}
                value={data[key]}
                onChange={(e) => setData(key, e.target.value)}
                className={inputClass}
            />
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
                            <p className="mt-1 text-[12px] font-bold text-muted">{t.mine}</p>
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

                            <fieldset className={listingSection}>
                                <legend className={listingHeading}>{t.contact}</legend>
                                <div className="grid gap-4 md:grid-cols-3">
                                    {contact("name", t.name, true)}
                                    {contact("phone", t.phone, true)}
                                    {contact("email", t.email, false, "email")}
                                </div>
                            </fieldset>

                            <ListingFields
                                data={data as never}
                                setData={(k, v) => setData(k as never, v as never)}
                                errors={errors}
                                options={options}
                            />

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
