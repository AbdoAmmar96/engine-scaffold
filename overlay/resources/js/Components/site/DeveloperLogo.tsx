/**
 * لوجو المطوّر — أو أول حرف من اسمه لو مفيش لوجو.
 * مربع مكسور في صفحة شركاء أسوأ من حرف مرسوم.
 */
export default function DeveloperLogo({
    name,
    logo,
    size = 56,
}: {
    name: string;
    logo?: string;
    size?: number;
}) {
    const box = { width: size, height: size };

    if (logo) {
        return (
            <span
                style={box}
                className="flex shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-100 bg-bg p-2"
            >
                <img src={logo} alt={name} loading="lazy" className="h-full w-full object-contain" />
            </span>
        );
    }

    return (
        <span
            style={{ ...box, fontSize: size * 0.4 }}
            className="flex shrink-0 items-center justify-center rounded-2xl bg-primary/10 font-black text-primary"
            aria-hidden="true"
        >
            {name.trim().charAt(0)}
        </span>
    );
}
