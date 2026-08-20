import { router, usePage } from "@inertiajs/react";
import { Building2, CheckCircle2, ChevronDown, Clock, Mail, MessageCircle, Phone, Star } from "lucide-react";
import { useState } from "react";
import PageHero from "@/Components/site/PageHero";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { ContactOptions, SharedProps } from "@/lib/types";

/** website حقل مصيدة للبوتس — مخفي في الفورم */
const emptyForm = { name: "", phone: "", area: "", budget: "", details: "", website: "" };

const copy = {
    ar: {
        crumb: "اتصل بنا",
        title: "اتصل بنا",
        desc: "اكتب اللي بتدور عليه وميزانيتك، وهنرجعلك في خلال ساعتين عمل بقائمة مبدئية.",
        formTitle: "اطلب مكالمة",
        name: "الاسم",
        phone: "رقم الموبايل",
        area: "المنطقة المطلوبة",
        budget: "الميزانية",
        details: "تفاصيل إضافية",
        detailsPh: "عدد الغرف، دور معيّن، استلام فوري…",
        submit: "ابعت الطلب",
        sending: "جارٍ الإرسال…",
        note: "مفيش مكالمات تسويقية. بياناتك بتفضل عندنا.",
        sentTitle: "استلمنا طلبك",
        sentText: "هنكلمك من رقم واحد فقط",
        again: "ابعت طلب تاني",
        infoPhone: "تليفون",
        infoWa: "واتساب",
        infoMail: "إيميل",
        infoHours: "مواعيد العمل",
        hours: "السبت – الخميس · 10ص – 8م",
        offices: "مكاتبنا",
        reviews: "تقييمات جوجل",
        reviewsText: "شوف تجارب عملاء اشتروا معانا فعلًا — كل التقييمات على جوجل من غير فلترة.",
        reviewsRead: "اقرأ التقييمات",
        reviewsWrite: "قيّمنا على جوجل",
        waCta: "كلّمنا واتساب",
        stepsTitle: "بعد ما تبعت الطلب",
        stepsSub: "ثلاث خطوات ثابتة، ومستشار واحد مسؤول عن ملفك من أول مكالمة.",
        faqTitle: "أسئلة شائعة",
        faqSub: "أكتر حاجات بيسألوا عنها قبل أول مكالمة.",
    },
    en: {
        crumb: "Contact",
        title: "Contact us",
        desc: "Tell us what you are looking for and your budget, and we will come back within two working hours with an initial shortlist.",
        formTitle: "Request a call",
        name: "Name",
        phone: "Mobile number",
        area: "Preferred area",
        budget: "Budget",
        details: "Extra details",
        detailsPh: "Number of rooms, a specific floor, ready to move…",
        submit: "Send the request",
        sending: "Sending…",
        note: "No marketing calls. Your data stays with us.",
        sentTitle: "We received your request",
        sentText: "We will call you from one number only",
        again: "Send another request",
        infoPhone: "Phone",
        infoWa: "WhatsApp",
        infoMail: "Email",
        infoHours: "Working hours",
        hours: "Saturday – Thursday · 10am – 8pm",
        offices: "Our offices",
        reviews: "Google reviews",
        reviewsText: "See what clients who actually bought with us said — every review on Google, unfiltered.",
        reviewsRead: "Read reviews",
        reviewsWrite: "Review us on Google",
        waCta: "WhatsApp us",
        stepsTitle: "After you send the request",
        stepsSub: "Three fixed steps, and one advisor responsible for your file from the first call.",
        faqTitle: "Frequently asked",
        faqSub: "The questions people ask most before the first call.",
    },
};

export default function Contact({ options }: { options: ContactOptions }) {
    const { locale, settings } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const t = copy[locale] ?? copy.ar;
    const contact = settings.contact ?? {};
    const wa = contact.whatsapp;
    const placeId = settings.integrations?.google_place_id;

    const [sent, setSent] = useState(false);
    const [sending, setSending] = useState(false);
    const [openFaq, setOpenFaq] = useState<number | null>(0);
    const [form, setForm] = useState(emptyForm);

    const set = (k: keyof typeof form) => (e: { target: { value: string } }) =>
        setForm((f) => ({ ...f, [k]: e.target.value }));

    // الطلب بيتسجّل في اللوحة الأول (موديول Leads) وبعدين بيفتح واتساب،
    // فالليد موجود عندنا حتى لو العميل مكمّلش المحادثة.
    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        setSending(true);

        router.post(
            `/${locale}/leads`,
            { ...form, message: form.details, source: "contact" },
            {
                preserveScroll: true,
                onFinish: () => setSending(false),
                onSuccess: () => {
                    if (wa) {
                        const lines = [
                            `${t.name}: ${form.name}`,
                            `${t.phone}: ${form.phone}`,
                            `${t.area}: ${form.area}`,
                            `${t.budget}: ${form.budget}`,
                            form.details && `${t.details}: ${form.details}`,
                        ].filter(Boolean);
                        window.open(`https://wa.me/${wa}?text=${encodeURIComponent(lines.join("\n"))}`, "_blank");
                    }

                    setSent(true);
                },
            },
        );
    };

    const field = "w-full rounded-lg border border-gray-200 bg-bg px-4 py-3 text-sm font-bold text-text placeholder:font-medium placeholder:text-muted focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/25";
    const label = "text-xs font-extrabold text-secondary";

    const infoRow = (Icon: typeof Phone, title: string, value: string, href?: string) => (
        <div className="flex items-start gap-3">
            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <Icon size={18} />
            </span>
            <div className="flex flex-col gap-0.5">
                <span className="text-[11px] font-bold text-muted">{title}</span>
                {href ? (
                    <a href={href} dir="ltr" className="text-sm font-extrabold text-secondary transition hover:text-primary">
                        {value}
                    </a>
                ) : (
                    <span className="text-sm font-extrabold text-secondary">{value}</span>
                )}
            </div>
        </div>
    );

    return (
        <SiteLayout>
            <PageHero bg="/images/demo/bg-contact.jpg" crumb={t.crumb} title={t.title} desc={t.desc} />

            <section className="bg-bg px-4 py-14">
                <div className="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[1.15fr_1fr] lg:items-start">
                    {/* ---------- الفورم ---------- */}
                    <Reveal>
                        <div className="rounded-3xl border border-gray-100 bg-bg p-6 shadow-[0_8px_30px_rgba(11,18,32,0.05)] md:p-8">
                            {sent ? (
                                <div className="flex flex-col items-center py-10 text-center">
                                    <span className="flex h-16 w-16 items-center justify-center rounded-full bg-success/10 text-success">
                                        <CheckCircle2 size={32} />
                                    </span>
                                    <h2 className="mt-4 text-2xl font-extrabold text-secondary">{t.sentTitle}</h2>
                                    <p className="mt-2 text-sm text-muted">
                                        {t.sentText}
                                        {contact.phone && (
                                            <>
                                                :{" "}
                                                <span dir="ltr" className="font-extrabold text-secondary">
                                                    {contact.phone}
                                                </span>
                                            </>
                                        )}
                                    </p>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setSent(false);
                                            setForm(emptyForm);
                                        }}
                                        className="mt-6 rounded-brand border-2 border-secondary px-6 py-3 text-sm font-extrabold text-secondary transition hover:bg-secondary hover:text-white"
                                    >
                                        {t.again}
                                    </button>
                                </div>
                            ) : (
                                <form onSubmit={submit} className="flex flex-col gap-4">
                                    <h2 className="text-2xl font-extrabold text-secondary">{t.formTitle}</h2>

                                    {/* مصيدة بوتس — مخفية عن البني آدم وعن قارئ الشاشة */}
                                    <input
                                        type="text"
                                        name="website"
                                        value={form.website}
                                        onChange={set("website")}
                                        tabIndex={-1}
                                        autoComplete="off"
                                        aria-hidden
                                        className="absolute -left-[9999px] h-0 w-0 opacity-0"
                                    />

                                    <div className="grid gap-4 sm:grid-cols-2">
                                        <label className="flex flex-col gap-2">
                                            <span className={label}>{t.name}</span>
                                            <input required value={form.name} onChange={set("name")} className={field} />
                                        </label>
                                        <label className="flex flex-col gap-2">
                                            <span className={label}>{t.phone}</span>
                                            <input
                                                required
                                                type="tel"
                                                dir="ltr"
                                                value={form.phone}
                                                onChange={set("phone")}
                                                className={field}
                                            />
                                        </label>
                                        <label className="flex flex-col gap-2">
                                            <span className={label}>{t.area}</span>
                                            <select required value={form.area} onChange={set("area")} className={field}>
                                                <option value="" disabled />
                                                {options.areas.map((v) => (
                                                    <option key={v}>{v}</option>
                                                ))}
                                            </select>
                                        </label>
                                        <label className="flex flex-col gap-2">
                                            <span className={label}>{t.budget}</span>
                                            <select required value={form.budget} onChange={set("budget")} className={field}>
                                                <option value="" disabled />
                                                {options.budgets.map((v) => (
                                                    <option key={v}>{v}</option>
                                                ))}
                                            </select>
                                        </label>
                                    </div>

                                    <label className="flex flex-col gap-2">
                                        <span className={label}>{t.details}</span>
                                        <textarea
                                            rows={4}
                                            placeholder={t.detailsPh}
                                            value={form.details}
                                            onChange={set("details")}
                                            className={field}
                                        />
                                    </label>

                                    <button
                                        type="submit"
                                        disabled={sending}
                                        className="rounded-brand bg-primary px-8 py-3.5 text-sm font-extrabold text-primary-fg transition hover:bg-primary-hover disabled:opacity-60"
                                    >
                                        {sending ? t.sending : t.submit}
                                    </button>

                                    <p className="text-xs font-bold text-muted">{t.note}</p>
                                </form>
                            )}
                        </div>
                    </Reveal>

                    {/* ---------- بيانات التواصل ---------- */}
                    <Reveal delay={130}>
                        <div className="flex flex-col gap-6">
                            <div className="grid gap-5 rounded-3xl border border-gray-100 bg-surface p-6 sm:grid-cols-2">
                                {contact.phone && infoRow(Phone, t.infoPhone, contact.phone, `tel:${contact.phone}`)}
                                {wa && infoRow(MessageCircle, t.infoWa, `+${wa}`, `https://wa.me/${wa}`)}
                                {contact.email && infoRow(Mail, t.infoMail, contact.email, `mailto:${contact.email}`)}
                                {infoRow(Clock, t.infoHours, t.hours)}
                            </div>

                            <div className="rounded-3xl border border-gray-100 bg-bg p-6">
                                <h3 className="text-lg font-extrabold text-secondary">{t.offices}</h3>
                                <div className="mt-4 flex flex-col gap-4">
                                    {options.offices.map((o) => (
                                        <div key={o.title} className="flex items-start gap-3">
                                            <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                                <Building2 size={18} />
                                            </span>
                                            <div>
                                                <div className="text-sm font-extrabold text-secondary">{o.title}</div>
                                                <p className="mt-1 text-xs leading-[1.8] text-muted">{o.text}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {placeId && (
                                <div className="rounded-3xl border border-gray-100 bg-bg p-6">
                                    <h3 className="flex items-center gap-2 text-lg font-extrabold text-secondary">
                                        <Star size={18} className="fill-primary text-primary" />
                                        {t.reviews}
                                    </h3>
                                    <p className="mt-2 text-xs leading-[1.8] text-muted">{t.reviewsText}</p>

                                    <div className="mt-4 flex flex-wrap gap-2">
                                        <a
                                            href={`https://search.google.com/local/reviews?placeid=${placeId}`}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="rounded-brand bg-secondary px-4 py-2.5 text-xs font-extrabold text-white transition hover:opacity-90"
                                        >
                                            {t.reviewsRead}
                                        </a>
                                        <a
                                            href={`https://search.google.com/local/writereview?placeid=${placeId}`}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="rounded-brand border-2 border-primary px-4 py-2.5 text-xs font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg"
                                        >
                                            {t.reviewsWrite}
                                        </a>
                                    </div>
                                </div>
                            )}

                            {wa && (
                                <a
                                    href={`https://wa.me/${wa}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="flex items-center justify-center gap-2 rounded-3xl border border-primary/30 bg-primary/10 px-6 py-5 text-sm font-extrabold text-secondary transition hover:bg-primary hover:text-primary-fg"
                                >
                                    <MessageCircle size={17} />
                                    {t.waCta}
                                </a>
                            )}
                        </div>
                    </Reveal>
                </div>
            </section>

            {/* ---------- بعد ما تبعت الطلب ---------- */}
            <section className="bg-surface px-4 py-14">
                <div className="mx-auto max-w-7xl">
                    <Reveal>
                        <h2 className="text-3xl font-extrabold text-secondary">{t.stepsTitle}</h2>
                        <p className="mt-2 max-w-xl text-base leading-[1.8] text-muted">{t.stepsSub}</p>
                    </Reveal>

                    <div className="mt-8 grid gap-6 md:grid-cols-3">
                        {options.steps.map((s, i) => (
                            <Reveal key={s.title} delay={i * 110}>
                                <div className="h-full rounded-2xl border border-gray-100 bg-bg p-6">
                                    <span
                                        dir="ltr"
                                        className="flex h-11 w-11 items-center justify-center rounded-full border border-primary/40 bg-primary/10 text-sm font-black text-secondary"
                                    >
                                        0{i + 1}
                                    </span>
                                    <h3 className="mt-4 text-[17px] font-extrabold text-secondary">{s.title}</h3>
                                    <p className="mt-2 text-sm leading-[1.85] text-muted">{s.text}</p>
                                </div>
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>

            {/* ---------- أسئلة شائعة ---------- */}
            <section className="bg-bg px-4 py-14">
                <div className="mx-auto max-w-3xl">
                    <Reveal>
                        <h2 className="text-3xl font-extrabold text-secondary">{t.faqTitle}</h2>
                        <p className="mt-2 text-base leading-[1.8] text-muted">{t.faqSub}</p>
                    </Reveal>

                    <div className="mt-8 flex flex-col gap-3">
                        {options.faq.map((item, i) => {
                            const isOpen = openFaq === i;
                            return (
                                <Reveal key={item.q} delay={i * 60}>
                                    <div
                                        className={`overflow-hidden rounded-2xl border transition ${
                                            isOpen ? "border-primary/50 bg-surface" : "border-gray-100 bg-bg"
                                        }`}
                                    >
                                        <button
                                            type="button"
                                            onClick={() => setOpenFaq(isOpen ? null : i)}
                                            aria-expanded={isOpen}
                                            className="flex w-full items-center justify-between gap-4 px-5 py-4 text-start"
                                        >
                                            <span className="text-[15px] font-extrabold text-secondary">{item.q}</span>
                                            <ChevronDown
                                                size={18}
                                                className={`shrink-0 text-primary transition-transform ${isOpen ? "rotate-180" : ""}`}
                                            />
                                        </button>
                                        {isOpen && (
                                            <p className="px-5 pb-5 text-sm leading-[1.9] text-muted">{item.a}</p>
                                        )}
                                    </div>
                                </Reveal>
                            );
                        })}
                    </div>
                </div>
            </section>
        </SiteLayout>
    );
}
