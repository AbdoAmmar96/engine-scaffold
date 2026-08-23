import { Quote, Star } from "lucide-react";
import Reveal from "@/Components/site/Reveal";

export type ReviewCard = {
    id: number;
    author: string;
    role: string;
    body: string;
    rating: number;
    avatar: string | null;
    date: string;
};

/**
 * آراء العملاء.
 *
 * القسم بيرجّع `null` وهو فاضي — مفيش نص بديل ولا كروت وهمية. التقييمات
 * أكتر محتوى بيتزوّر في المواقع العقارية، والقسم الفاضي أصدق من رأي متلفّق.
 */
export default function Reviews({ items, title, desc }: { items: ReviewCard[]; title: string; desc?: string }) {
    if (items.length === 0) {
        return null;
    }

    return (
        <section className="bg-surface px-4 py-14">
            <div className="mx-auto max-w-7xl">
                <Reveal>
                    <h2 className="text-3xl font-extrabold text-secondary">{title}</h2>
                    {desc && <p className="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-muted">{desc}</p>}
                </Reveal>

                <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {items.map((r, i) => (
                        <Reveal key={r.id} delay={i * 90}>
                            <article className="flex h-full flex-col rounded-2xl border border-gray-100 bg-bg p-6">
                                <Quote size={22} className="shrink-0 text-primary/40" aria-hidden />

                                <p className="mt-3 flex-1 text-sm leading-[2] text-text">{r.body}</p>

                                <div
                                    className="mt-5 flex items-center gap-1"
                                    role="img"
                                    aria-label={`${r.rating} من 5`}
                                >
                                    {[1, 2, 3, 4, 5].map((n) => (
                                        <Star
                                            key={n}
                                            size={14}
                                            aria-hidden
                                            className={n <= r.rating ? "fill-primary text-primary" : "text-gray-300"}
                                        />
                                    ))}
                                </div>

                                <div className="mt-4 flex items-center gap-3 border-t border-border pt-4">
                                    {r.avatar ? (
                                        <img
                                            src={r.avatar}
                                            alt=""
                                            loading="lazy"
                                            className="h-10 w-10 shrink-0 rounded-full object-cover"
                                        />
                                    ) : (
                                        /* مفيش صورة — أول حرف بدل مربع مكسور */
                                        <span
                                            aria-hidden
                                            className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-secondary text-sm font-extrabold text-white"
                                        >
                                            {r.author.trim().charAt(0)}
                                        </span>
                                    )}
                                    <span className="min-w-0">
                                        <span className="block truncate text-sm font-extrabold text-secondary">
                                            {r.author}
                                        </span>
                                        {(r.role || r.date) && (
                                            <span className="block truncate text-[11px] font-bold text-muted">
                                                {[r.role, r.date].filter(Boolean).join(" · ")}
                                            </span>
                                        )}
                                    </span>
                                </div>
                            </article>
                        </Reveal>
                    ))}
                </div>
            </div>
        </section>
    );
}
