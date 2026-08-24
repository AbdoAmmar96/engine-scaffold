import { Link, usePage } from "@inertiajs/react";
import { ArrowLeft, Building2 } from "lucide-react";
import CountUp from "@/Components/site/CountUp";
import PageHero from "@/Components/site/PageHero";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { DeveloperCard, Milestone, SharedProps, TeamMember } from "@/lib/types";

const copy = {
    ar: {
        crumb: "من نحن",
        // العنوان والوصف الافتراضيين محايدين عن قصد: أي رقم أو تاريخ هنا
        // كان هيبقى ادعاء محدش راجعه. الأدمن بيكتب نصه من إعدادات «صفحة من نحن».
        title: "من نحن",
        desc: "نعرض وحدات ومشاريع عقارية، ونوصّلك بصاحبها أو مطوّرها.",
        pledgeLabel: "ما يميّزنا",
        clientsLabel: "عميل أتمّ التعاقد",
        teamLabel: "فرد في الفريق",
        compoundsLabel: "كمبوند في القائمة",
        developersLabel: "مطوّر شريك",
        milestonesTitle: "المحطات",
        teamTitle: "الفريق",
        devsTitle: "المطوّرون على المنصة",
        devsDesc: "المطوّرون الذين نعرض مشاريعهم حاليًا — اضغط على أي منهم لعرض مشاريعه.",
        devsProjects: (n: number) => (n === 1 ? "مشروع واحد" : n === 2 ? "مشروعان" : `${n} مشاريع`),
        devsCta: "عرض المشاريع",
    },
    en: {
        crumb: "About",
        title: "About us",
        desc: "We list real-estate units and projects, and connect you with their owner or developer.",
        pledgeLabel: "What sets us apart",
        clientsLabel: "Clients contracted",
        teamLabel: "Team members",
        compoundsLabel: "Compounds listed",
        developersLabel: "Partner developers",
        milestonesTitle: "Milestones",
        teamTitle: "The team",
        devsTitle: "Developers on the platform",
        devsDesc: "The developers whose projects we currently list — pick one to see its projects.",
        devsProjects: (n: number) => `${n} ${n === 1 ? "project" : "projects"}`,
        devsCta: "View projects",
    },
};

/** كل حاجة هنا بتتكتب من `/admin/settings/about` — والفاضي بيخفي قسمه */
type AboutContent = {
    heroTitle: string;
    heroDesc: string;
    pledgeTitle: string;
    pledge: string[];
    milestones: Milestone[];
    team: TeamMember[];
};

export default function About({ content, stats: counts0, developers }: {
    content: AboutContent;
    stats: { value: string; label: string }[];
    developers: DeveloperCard[];
}) {
    const { locale, settings } = usePage<SharedProps>().props;
    const t = copy[locale] ?? copy.ar;

    // الكمبوندات والمطوّرين معدودين من الداتابيز، والعملاء وحجم الفريق من
    // الإعدادات (فاضيين افتراضيًا). أي رقم مش متوفّر بيختفي بدل ما يتلفّق.
    const counts = (counts0 ?? []).map((c) => c.value);
    const pair = (value: string | undefined, label: string): [string, string][] =>
        value ? [[value, label]] : [];

    const stats: [string, string][] = [
        ...pair(settings.general?.clients_served, t.clientsLabel),
        ...pair(settings.general?.team_size, t.teamLabel),
        ...pair(counts[1], t.compoundsLabel),
        ...pair(counts[2], t.developersLabel),
    ];

    return (
        <SiteLayout>
            <PageHero
                bg="/images/demo/bg-about.jpg"
                crumb={t.crumb}
                title={content.heroTitle || t.title}
                desc={content.heroDesc || t.desc}
            />

            {/* ---------- التعهّد (بيختفي لو الأدمن ماكتبش نصه) ---------- */}
            {content.pledge.length > 0 && (
            <section className="bg-bg px-4 py-14">
                {/* عمود واحد: النص تحت العنوان مش جنبه. العمودين كانوا بيبعدوا
                    العنوان عن نصّه، وسطر قصير كان بيسيب نص الشاشة فاضي جنبه */}
                <div className="mx-auto max-w-7xl">
                    <Reveal>
                        <span className="inline-flex items-center rounded-full border border-primary/40 bg-primary/10 px-4 py-2 text-[11px] font-extrabold text-secondary">
                            {t.pledgeLabel}
                        </span>
                        {content.pledgeTitle && (
                            <h2 className="mt-4 text-3xl font-extrabold leading-[1.5] text-secondary">
                                {content.pledgeTitle}
                            </h2>
                        )}
                        <span className="mt-5 block h-1 w-16 rounded-full bg-primary" aria-hidden />
                    </Reveal>
                    <Reveal delay={120}>
                        {/* عرض محدود للقراءة — السطر الطويل أوي بيتعب العين */}
                        <div className="mt-6 max-w-3xl">
                            {content.pledge.map((para, i) => (
                                <p key={i} className={`text-base leading-[1.95] text-muted${i > 0 ? " mt-4" : ""}`}>
                                    {para}
                                </p>
                            ))}
                        </div>
                    </Reveal>
                </div>
            </section>
            )}

            {/* ---------- الأرقام (بتختفي كلها لو مفيش ولا رقم متوفّر) ---------- */}
            {stats.length > 0 && (
            <section className="bg-surface px-4 py-14">
                <div className="mx-auto grid max-w-7xl gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {stats.map(([value, label], i) => (
                        <Reveal key={label} delay={i * 90}>
                            <div className="rounded-2xl border border-gray-100 bg-bg p-6 text-center">
                                <div className="text-[32px] font-black text-primary" dir="ltr">
                                    <CountUp value={value} />
                                </div>
                                <div className="mt-1 text-xs font-bold text-muted">{label}</div>
                            </div>
                        </Reveal>
                    ))}
                </div>
            </section>
            )}

            {/* ---------- المحطات (بتختفي لو الأدمن ماكتبش ولا محطة) ---------- */}
            {content.milestones.length > 0 && (
            <section className="bg-bg px-4 py-14">
                <div className="mx-auto max-w-7xl">
                    <Reveal>
                        <h2 className="mb-8 text-3xl font-extrabold text-secondary">{t.milestonesTitle}</h2>
                    </Reveal>

                    <div className="relative">
                        {/* الخط الزمني */}
                        <span className="absolute inset-y-0 start-[18px] w-px bg-gray-200 lg:inset-x-0 lg:inset-y-auto lg:top-[18px] lg:h-px lg:w-full" />

                        <div className="grid gap-8 lg:grid-cols-4">
                            {content.milestones.map((m, i) => (
                                <Reveal key={m.year} delay={i * 110}>
                                    <div className="relative ps-12 lg:ps-0">
                                        <span className="absolute start-0 top-0 flex h-9 w-9 items-center justify-center rounded-full border border-primary/40 bg-primary/10 text-[11px] font-black text-secondary lg:relative lg:mb-5">
                                            {i + 1}
                                        </span>
                                        <div className="text-sm font-black text-primary" dir="ltr">
                                            {m.year}
                                        </div>
                                        <h3 className="mt-1 text-[17px] font-extrabold text-secondary">{m.title}</h3>
                                        <p className="mt-2 text-sm leading-[1.85] text-muted">{m.text}</p>
                                    </div>
                                </Reveal>
                            ))}
                        </div>
                    </div>
                </div>
            </section>
            )}

            {/* ---------- الفريق (بيختفي لو فاضي — صور ستوك بأسماء متلفّقة أسوأ من مفيش قسم) ---------- */}
            {content.team.length > 0 && (
            <section className="bg-surface px-4 py-14">
                <div className="mx-auto max-w-7xl">
                    <Reveal>
                        <h2 className="mb-8 text-3xl font-extrabold text-secondary">{t.teamTitle}</h2>
                    </Reveal>

                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {content.team.map((p, i) => (
                            <Reveal key={p.name} delay={i * 90}>
                                <article className="group overflow-hidden rounded-2xl border border-gray-100 bg-bg text-center transition duration-200 hover:-translate-y-1 hover:border-primary/50">
                                    <div className="aspect-square overflow-hidden bg-surface">
                                        <img
                                            src={p.image}
                                            alt={p.name}
                                            loading="lazy"
                                            className="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                        />
                                    </div>
                                    <div className="p-4">
                                        <h3 className="text-base font-extrabold text-secondary">{p.name}</h3>
                                        <p className="mt-1 text-xs font-bold text-muted">{p.role}</p>
                                    </div>
                                </article>
                            </Reveal>
                        ))}
                    </div>
                </div>
            </section>
            )}

            {/* المطوّرون — بيتقروا من الداتابيز، فالقسم كله بيختفي لو مفيش ولا واحد بمشاريع منشورة */}
            {developers.length > 0 && (
                <section className="bg-bg px-4 py-14">
                    <div className="mx-auto max-w-7xl">
                        <Reveal>
                            <h2 className="text-3xl font-extrabold text-secondary">{t.devsTitle}</h2>
                            <p className="mt-2 max-w-2xl text-sm font-bold leading-relaxed text-muted">{t.devsDesc}</p>
                        </Reveal>

                        <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {developers.map((d, i) => (
                                <Reveal key={d.id} delay={i * 90}>
                                    <Link
                                        href={d.url}
                                        className="group flex h-full flex-col rounded-2xl border border-gray-100 bg-surface p-6 transition duration-200 hover:-translate-y-1 hover:border-primary/50"
                                    >
                                        <div className="flex items-center gap-4">
                                            {d.logo ? (
                                                <img
                                                    src={d.logo}
                                                    alt={d.name}
                                                    loading="lazy"
                                                    className="h-14 w-14 shrink-0 rounded-xl object-contain"
                                                />
                                            ) : (
                                                /* اللوجو لسه مترفعش — أول حرف بدل مربع مكسور */
                                                <span
                                                    aria-hidden
                                                    className="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-secondary text-2xl font-extrabold text-white"
                                                >
                                                    {d.name.trim().charAt(0)}
                                                </span>
                                            )}

                                            <div className="min-w-0">
                                                <h3 className="truncate text-lg font-extrabold text-secondary transition group-hover:text-primary">
                                                    {d.name}
                                                </h3>
                                                <span className="mt-1 flex items-center gap-2 text-xs font-bold text-muted">
                                                    <Building2 size={13} className="shrink-0 text-primary" />
                                                    {t.devsProjects(d.compounds)}
                                                </span>
                                            </div>
                                        </div>

                                        {d.about && (
                                            <p className="mt-4 line-clamp-3 text-sm font-bold leading-relaxed text-muted">
                                                {d.about}
                                            </p>
                                        )}

                                        <span className="mt-auto flex items-center gap-2 pt-5 text-[13px] font-extrabold text-primary">
                                            {t.devsCta}
                                            <ArrowLeft size={15} className="transition group-hover:-translate-x-1 ltr:rotate-180 ltr:group-hover:translate-x-1" />
                                        </span>
                                    </Link>
                                </Reveal>
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </SiteLayout>
    );
}
