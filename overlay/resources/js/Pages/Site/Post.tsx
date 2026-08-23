import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, CalendarDays, Clock, Share2, User } from "lucide-react";
import PageHero from "@/Components/site/PageHero";
import Reveal from "@/Components/site/Reveal";
import RichText from "@/Components/site/RichText";
import SiteLayout from "@/Layouts/SiteLayout";
import type { BlogArticle, BlogPost, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "المدونة",
        back: "كل المقالات",
        read: "دقيقة قراءة",
        more: "اقرأ المزيد",
        share: "انسخ الرابط",
        copied: "تم نسخ الرابط ✅",
        cta: "محتاج رأي متخصص في وحدة معيّنة؟",
        ctaDesc: "أرسل إلينا الميزانية والمنطقة، وسنرشّح لك ما يناسبك فعلًا — وسنقول لك «لا» إن لم يكن هناك ما يناسب.",
        ctaBtn: "تواصل معنا",
    },
    en: {
        crumb: "Blog",
        back: "All articles",
        read: "min read",
        more: "Read next",
        share: "Copy link",
        copied: "Link copied ✅",
        cta: "Need a specialist opinion on a specific unit?",
        ctaDesc: "Send us your budget and area, and we'll shortlist what actually fits — and tell you \"no\" if nothing does.",
        ctaBtn: "Contact us",
    },
};

export default function Post({ post, more }: { post: BlogArticle; more: BlogPost[] }) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const share = () => {
        navigator.clipboard?.writeText(window.location.href).then(() => alert(t.copied));
    };

    return (
        <SiteLayout>
            <PageHero bg={post.image} crumb={t.crumb} title={post.title} desc={post.excerpt}>
                <div className="flex flex-wrap items-center gap-4 text-[12px] font-bold text-white/70">
                    {post.category && (
                        <span className="rounded-full bg-primary px-3 py-1.5 text-[11px] font-extrabold text-primary-fg">
                            {post.category}
                        </span>
                    )}
                    {post.author && (
                        <span className="flex items-center gap-1.5">
                            <User size={13} className="text-primary" />
                            {post.author}
                        </span>
                    )}
                    <span className="flex items-center gap-1.5">
                        <CalendarDays size={13} className="text-primary" />
                        {post.date}
                    </span>
                    <span className="flex items-center gap-1.5">
                        <Clock size={13} className="text-primary" />
                        <span dir="ltr">{post.read}</span>
                        {t.read}
                    </span>
                </div>
            </PageHero>

            <section className="bg-bg px-4 py-12">
                <div className="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[minmax(0,1fr)_20rem]">
                    <article className="min-w-0">
                        <Reveal>
                            <RichText text={post.body} />
                        </Reveal>

                        <div className="mt-10 flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-6">
                            <Link
                                href={`/${locale}/blog`}
                                className="flex items-center gap-2 text-[13px] font-extrabold text-secondary transition hover:text-primary"
                            >
                                <ArrowLeft size={15} className="text-primary ltr:rotate-180" />
                                {t.back}
                            </Link>

                            <button
                                onClick={share}
                                className="flex items-center gap-2 rounded-brand border border-gray-200 px-4 py-2.5 text-[13px] font-extrabold text-secondary transition hover:border-primary hover:text-primary"
                            >
                                <Share2 size={15} />
                                {t.share}
                            </button>
                        </div>
                    </article>

                    <aside className="flex flex-col gap-6">
                        <div className="rounded-2xl bg-bg-dark p-6">
                            <h3 className="text-base font-extrabold leading-[1.7] text-white">{t.cta}</h3>
                            <p className="mt-2 text-[13px] leading-[1.9] text-white/65">{t.ctaDesc}</p>
                            <Link
                                href={`/${locale}/contact`}
                                className="mt-4 block rounded-brand bg-primary py-3 text-center text-[13px] font-extrabold text-primary-fg transition hover:opacity-90"
                            >
                                {t.ctaBtn}
                            </Link>
                        </div>

                        {more.length > 0 && (
                            <div>
                                <h3 className="mb-3 text-sm font-extrabold text-secondary">{t.more}</h3>
                                <div className="flex flex-col gap-3">
                                    {more.map((p) => (
                                        <Link
                                            key={p.id}
                                            href={`/${locale}/blog/${p.slug}`}
                                            className="group flex gap-3 rounded-2xl border border-gray-100 p-3 transition hover:border-primary/50 hover:bg-surface"
                                        >
                                            <img
                                                src={p.image}
                                                alt=""
                                                loading="lazy"
                                                className="h-16 w-20 shrink-0 rounded-xl object-cover"
                                            />
                                            <div className="flex min-w-0 flex-col gap-1">
                                                <span className="line-clamp-2 text-[13px] font-extrabold leading-[1.6] text-secondary transition group-hover:text-primary">
                                                    {p.title}
                                                </span>
                                                <span className="text-[11px] font-bold text-muted">{p.date}</span>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </div>
                        )}
                    </aside>
                </div>
            </section>
        </SiteLayout>
    );
}
