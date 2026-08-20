import { usePage } from "@inertiajs/react";
import CountUp from "@/Components/site/CountUp";
import PageHero from "@/Components/site/PageHero";
import Reveal from "@/Components/site/Reveal";
import SiteLayout from "@/Layouts/SiteLayout";
import type { Milestone, SharedProps, TeamMember } from "@/lib/types";

const copy = {
    ar: {
        crumb: "من نحن",
        title: "اثنتا عشرة سنة في سوق واحد",
        desc: "بدأنا مكتب تسويق صغير في التجمع الخامس سنة 2014، ودلوقتي فريق من 46 شخص بيغطي القاهرة الجديدة والعاصمة الإدارية والإسكندرية.",
        pledgeLabel: "اللي بيفرّقنا",
        pledgeTitle: "بنقولك «لأ» لما الوحدة متناسبكش",
        pledge1:
            "مستشارينا مبياخدوش عمولة مربوطة بوحدة بعينها، فمفيش أي دافع يخليهم يدفعوك ناحية مشروع دون التاني. نصيحتهم مبنية على احتياجك وميزانيتك — مش على اللي هيكسّبهم أكتر.",
        pledge2:
            "والوحدة مبتدخلش قائمتنا غير بعد ثلاث خطوات: مراجعة الأوراق والتسجيل، ومعاينة على الأرض، ومطابقة السعر بآخر بيوعات المنطقة. أي وحدة بتسقط في خطوة، بتخرج من القائمة.",
        pledge3:
            "وبعد التعاقد الشغل مبيقفش — فريق ما بعد البيع بيتابع معاك الأقساط وتسليم الوحدة وتوثيق أي تعديل مع المطوّر.",
        clientsLabel: "عميل أتمّ التعاقد",
        teamLabel: "فرد في الفريق",
        compoundsLabel: "كمبوند في القائمة",
        developersLabel: "مطوّر شريك",
        milestonesTitle: "المحطات",
        teamTitle: "الفريق",
    },
    en: {
        crumb: "About",
        title: "Twelve years in one market",
        desc: "We started as a small marketing office in the Fifth Settlement in 2014, and today we are a team of 46 covering New Cairo, the New Capital and Alexandria.",
        pledgeLabel: "What sets us apart",
        pledgeTitle: "We tell you «no» when the unit does not fit you",
        pledge1:
            "Our advisors earn no commission tied to a specific unit, so nothing pushes them toward one project over another. Their advice is built on your needs and budget — not on what pays them more.",
        pledge2:
            "And a unit only enters our list after three steps: paperwork and registration review, an on-site viewing, and a price check against the area's latest sales. Any unit that fails a step leaves the list.",
        pledge3:
            "After the contract the work does not stop — the after-sales team follows up on instalments, the handover, and documenting any change with the developer.",
        clientsLabel: "Clients contracted",
        teamLabel: "Team members",
        compoundsLabel: "Compounds listed",
        developersLabel: "Partner developers",
        milestonesTitle: "Milestones",
        teamTitle: "The team",
    },
};

export default function About({ milestones, team, stats: counts0 }: { milestones: Milestone[]; team: TeamMember[]; stats: { value: string; label: string }[] }) {
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
            <PageHero bg="/images/demo/bg-about.jpg" crumb={t.crumb} title={t.title} desc={t.desc} />

            {/* ---------- التعهّد ---------- */}
            <section className="bg-bg px-4 py-14">
                <div className="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[1fr_1.1fr] lg:items-start">
                    <Reveal>
                        <span className="inline-flex items-center rounded-full border border-primary/40 bg-primary/10 px-4 py-2 text-[11px] font-extrabold text-secondary">
                            {t.pledgeLabel}
                        </span>
                        <h2 className="mt-4 text-3xl font-extrabold leading-[1.5] text-secondary">{t.pledgeTitle}</h2>
                        <span className="mt-5 block h-1 w-16 rounded-full bg-primary" aria-hidden />
                    </Reveal>
                    <Reveal delay={120}>
                        <p className="text-base leading-[1.95] text-muted">{t.pledge1}</p>
                        <p className="mt-4 text-base leading-[1.95] text-muted">{t.pledge2}</p>
                        <p className="mt-4 text-base leading-[1.95] text-muted">{t.pledge3}</p>
                    </Reveal>
                </div>
            </section>

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

            {/* ---------- المحطات ---------- */}
            <section className="bg-bg px-4 py-14">
                <div className="mx-auto max-w-7xl">
                    <Reveal>
                        <h2 className="mb-8 text-3xl font-extrabold text-secondary">{t.milestonesTitle}</h2>
                    </Reveal>

                    <div className="relative">
                        {/* الخط الزمني */}
                        <span className="absolute inset-y-0 start-[18px] w-px bg-gray-200 lg:inset-x-0 lg:inset-y-auto lg:top-[18px] lg:h-px lg:w-full" />

                        <div className="grid gap-8 lg:grid-cols-4">
                            {milestones.map((m, i) => (
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

            {/* ---------- الفريق ---------- */}
            <section className="bg-surface px-4 py-14">
                <div className="mx-auto max-w-7xl">
                    <Reveal>
                        <h2 className="mb-8 text-3xl font-extrabold text-secondary">{t.teamTitle}</h2>
                    </Reveal>

                    <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {team.map((p, i) => (
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
        </SiteLayout>
    );
}
