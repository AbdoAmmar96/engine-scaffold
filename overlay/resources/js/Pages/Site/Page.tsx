import { usePage } from "@inertiajs/react";
import { CalendarDays } from "lucide-react";
import PageHero from "@/Components/site/PageHero";
import Reveal from "@/Components/site/Reveal";
import RichText from "@/Components/site/RichText";
import SiteLayout from "@/Layouts/SiteLayout";
import type { SharedProps } from "@/lib/types";

const copy = {
    ar: { crumb: "صفحة", updated: "آخر تحديث" },
    en: { crumb: "Page", updated: "Last updated" },
};

type ContentPage = {
    title: string;
    slug: string;
    excerpt: string;
    body: string;
    updatedAt: string | null;
    updatedLabel: string;
};

/**
 * صفحة محتوى ثابت — سياسة الخصوصية، الشروط، وأي صفحة نصية الأدمن بيضيفها.
 *
 * عمود واحد ضيّق عن قصد: ده نص طويل بيتقرا، والسطر اللي أعرض من ~75 حرف
 * بيصعّب على العين ترجع لأول السطر اللي بعده.
 */
export default function Page({ page }: { page: ContentPage }) {
    const { locale } = usePage<SharedProps>().props;
    const t = copy[locale === "en" ? "en" : "ar"];

    return (
        <SiteLayout>
            <PageHero
                bg="/images/demo/bg-about.jpg"
                crumb={page.title || t.crumb}
                title={page.title}
                desc={page.excerpt || undefined}
            />

            <section className="bg-bg px-4 py-12 md:py-16">
                <div className="mx-auto max-w-3xl">
                    <Reveal>
                        <article className="rounded-3xl border border-border bg-white p-6 md:p-10">
                            {page.body ? (
                                <RichText text={page.body} />
                            ) : (
                                // صفحة منشورة ومحتواها فاضي: أحسن من صندوق فاضي
                                // إن الزائر يعرف إنه مش عطل في المتصفح
                                <p className="text-[15px] leading-[2.1] text-text-muted">
                                    {locale === "en" ? "This page has no content yet." : "الصفحة دي لسه مالهاش محتوى."}
                                </p>
                            )}

                            {page.updatedLabel && (
                                <p className="mt-8 flex items-center gap-2 border-t border-border pt-5 text-xs font-bold text-text-muted">
                                    <CalendarDays size={14} />
                                    {t.updated}:{" "}
                                    <time dateTime={page.updatedAt ?? undefined}>{page.updatedLabel}</time>
                                </p>
                            )}
                        </article>
                    </Reveal>
                </div>
            </section>
        </SiteLayout>
    );
}
