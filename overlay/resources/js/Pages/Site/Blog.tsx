import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, CalendarDays, Clock } from "lucide-react";
import { useMemo, useState } from "react";
import PageHero from "@/Components/site/PageHero";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { BlogPost, SharedProps } from "@/lib/types";

const copy = {
    ar: {
        crumb: "المدونة",
        title: "المدونة العقارية",
        desc: "تحليلات وأدلة عملية عن السوق العقاري المصري — أرقام وتواريخ يمكنك أن تبني عليها قرارك، لا كلام تسويقي.",
        all: "الكل",
        read: "دقيقة قراءة",
        more: "اقرأ المقال",
        empty: "لا توجد مقالات منشورة بعد — قريبًا.",
    },
    en: {
        crumb: "Blog",
        title: "Real-estate blog",
        desc: "Analysis and practical guides on the Egyptian property market — numbers and dates you can build a decision on, not marketing copy.",
        all: "All",
        read: "min read",
        more: "Read article",
        empty: "No articles published yet — coming soon.",
    },
};

export default function Blog({ posts }: { posts: BlogPost[] }) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    const categories = useMemo(
        () => [...new Set(posts.map((p) => p.category).filter(Boolean))],
        [posts],
    );

    const [active, setActive] = useState<string | null>(null);
    const shown = active ? posts.filter((p) => p.category === active) : posts;

    const [featured, ...rest] = shown;

    const meta = (post: BlogPost, light = false) => (
        <div className={`flex flex-wrap items-center gap-3 text-[11px] font-bold ${light ? "text-white/70" : "text-muted"}`}>
            <span className="flex items-center gap-1.5">
                <CalendarDays size={13} className="text-primary" />
                {post.date}
            </span>
            <span className="flex items-center gap-1.5">
                <Clock size={13} className="text-primary" />
                <span dir="ltr">{post.read}</span>
                {t.read}
            </span>
            {post.author && <span>· {post.author}</span>}
        </div>
    );

    return (
        <SiteLayout>
            <PageHero bg="/images/demo/bg-about.jpg" crumb={t.crumb} title={t.title} desc={t.desc} />

            <section className="bg-bg px-4 py-12">
                <div className="mx-auto max-w-7xl">
                    {posts.length === 0 && (
                        <p className="rounded-2xl border border-dashed border-gray-200 py-16 text-center text-sm font-bold text-muted">
                            {t.empty}
                        </p>
                    )}

                    {categories.length > 1 && (
                        <div className="mb-8 flex flex-wrap gap-2">
                            {[null, ...categories].map((c) => (
                                <button
                                    key={c ?? "all"}
                                    onClick={() => setActive(c)}
                                    className={`rounded-full px-4 py-2 text-[13px] font-extrabold transition ${
                                        active === c
                                            ? "bg-secondary text-white"
                                            : "border border-gray-200 bg-surface text-secondary hover:border-primary"
                                    }`}
                                >
                                    {c ?? t.all}
                                </button>
                            ))}
                        </div>
                    )}

                    {/* المقال الأحدث بعرض كامل */}
                    {featured && (
                        <Reveal>
                            <Link
                                href={`/${locale}/blog/${featured.slug}`}
                                className="group relative isolate mb-8 flex min-h-[22rem] flex-col justify-end overflow-hidden rounded-3xl p-6 md:p-10"
                            >
                                <img
                                    src={featured.image}
                                    alt={featured.title}
                                    className="absolute inset-0 -z-10 h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                />
                                <div className="absolute inset-0 -z-10 bg-gradient-to-t from-bg-dark/95 via-bg-dark/60 to-bg-dark/15" />

                                {featured.category && (
                                    <span className="mb-3 w-fit rounded-full bg-primary px-3 py-1.5 text-[11px] font-extrabold text-primary-fg">
                                        {featured.category}
                                    </span>
                                )}

                                <h2 className="max-w-3xl text-2xl font-black leading-[1.4] text-white md:text-4xl">
                                    {featured.title}
                                </h2>

                                {featured.excerpt && (
                                    <p className="mt-3 max-w-2xl text-sm leading-[1.9] text-white/75">{featured.excerpt}</p>
                                )}

                                <div className="mt-4">{meta(featured, true)}</div>
                            </Link>
                        </Reveal>
                    )}

                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {rest.map((post, i) => (
                            <Reveal key={post.id} delay={i * 90}>
                                <Link
                                    href={`/${locale}/blog/${post.slug}`}
                                    className="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-bg transition duration-200 hover:-translate-y-1 hover:border-primary/50 hover:shadow-[0_12px_30px_rgba(11,18,32,0.07)]"
                                >
                                    <div className="relative h-44 overflow-hidden bg-surface">
                                        <img
                                            src={post.image}
                                            alt={post.title}
                                            loading="lazy"
                                            className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        />
                                        {post.category && (
                                            <span className="absolute start-3 top-3 rounded-full bg-bg/95 px-3 py-1.5 text-[11px] font-extrabold text-secondary">
                                                {post.category}
                                            </span>
                                        )}
                                    </div>

                                    <div className="flex flex-1 flex-col gap-2.5 p-5">
                                        {meta(post)}

                                        <h3 className="text-lg font-extrabold leading-[1.6] text-secondary transition group-hover:text-primary">
                                            {post.title}
                                        </h3>

                                        {post.excerpt && (
                                            <p className="line-clamp-3 text-[13px] leading-[1.9] text-muted">{post.excerpt}</p>
                                        )}

                                        <span className="mt-auto flex items-center gap-1.5 pt-2 text-[13px] font-extrabold text-secondary">
                                            {t.more}
                                            <ArrowLeft size={15} className="text-primary transition group-hover:-translate-x-1 rtl:group-hover:translate-x-1 ltr:rotate-180" />
                                        </span>
                                    </div>
                                </Link>
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>
        </SiteLayout>
    );
}
