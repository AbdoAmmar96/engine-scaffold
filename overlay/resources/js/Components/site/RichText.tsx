import type { ReactNode } from "react";

/**
 * نص عادي → فقرات وعناوين فرعية ونقاط.
 *
 * صيغة واحدة للمدونة وصفحات المحتوى: السطر اللي بيبدأ بـ `## ` عنوان فرعي،
 * واللي بيبدأ بـ `- ` نقطة، والسطر الفاضي بيفصل فقرة. الأدمن بيتعلّمها مرة.
 *
 * ليه مش Markdown كامل ولا HTML: المحتوى بيتكتب من اللوحة، وHTML خام من
 * حقل نصي معناه XSS. الصيغة دي بتترندر كـ JSX — مفيش dangerouslySetInnerHTML
 * ولا حاجة بتتنفّذ.
 */
export function toBlocks(text: string): string[] {
    const blocks: string[] = [];
    let buf: string[] = [];

    const flush = () => {
        const joined = buf.join("\n").trim();
        if (joined) blocks.push(joined);
        buf = [];
    };

    for (const raw of text.split("\n")) {
        const line = raw.trim();

        if (!line) {
            flush();
            continue;
        }

        if (line.startsWith("## ")) {
            flush();
            blocks.push(line);
            continue;
        }

        // التبديل بين فقرة ونقط بيبدأ بلوك جديد
        if (line.startsWith("- ") !== (buf.length > 0 && buf[0].startsWith("- "))) flush();

        buf.push(line);
    }

    flush();

    return blocks;
}

/**
 * `**نص**` → تأكيد. بيرندر كـ JSX مش HTML، فمفيش أي حاجة بتتنفّذ حتى لو
 * الأدمن لصق وسوم في الحقل.
 */
function inline(text: string): ReactNode[] {
    return text.split(/(\*\*[^*]+\*\*)/g).map((part, i) =>
        part.startsWith("**") && part.endsWith("**") && part.length > 4 ? (
            <strong key={i} className="font-extrabold text-secondary">
                {part.slice(2, -2)}
            </strong>
        ) : (
            part
        ),
    );
}

export default function RichText({ text, className = "" }: { text: string; className?: string }) {
    const blocks = toBlocks(text);

    return (
        <div className={`flex flex-col gap-5 ${className}`}>
            {blocks.map((block, i) => {
                if (block.startsWith("## ")) {
                    return (
                        <h2 key={i} className="mt-4 text-xl font-extrabold leading-[1.6] text-secondary md:text-2xl">
                            {inline(block.slice(3))}
                        </h2>
                    );
                }

                const lines = block.split("\n").map((l) => l.trim());

                if (lines.every((l) => l.startsWith("- "))) {
                    return (
                        <ul key={i} className="flex flex-col gap-2.5">
                            {lines.map((l, j) => (
                                <li key={j} className="flex gap-3 text-[15px] leading-[2] text-text">
                                    <span className="mt-2.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" />
                                    <span>{inline(l.slice(2))}</span>
                                </li>
                            ))}
                        </ul>
                    );
                }

                return (
                    <p key={i} className="text-[15px] leading-[2.1] text-text">
                        {inline(block)}
                    </p>
                );
            })}
        </div>
    );
}
