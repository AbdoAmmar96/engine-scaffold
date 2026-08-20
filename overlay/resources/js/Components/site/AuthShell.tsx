import { Link, usePage } from "@inertiajs/react";
import type { ReactNode } from "react";
import SiteLayout from "@/Layouts/SiteLayout";
import type { SharedProps } from "@/lib/types";

/** إطار مشترك لشاشتي الدخول والاشتراك — كارت في النص فوق خلفية الهوية */
export default function AuthShell({
    title,
    desc,
    footer,
    children,
}: {
    title: string;
    desc: string;
    footer: ReactNode;
    children: ReactNode;
}) {
    const { locale } = usePage<SharedProps>().props;
    const ar = locale === "ar";

    return (
        <SiteLayout>
            <section className="relative isolate flex min-h-[70vh] items-center justify-center overflow-hidden px-4 py-16">
                <div className="absolute inset-0 -z-10">
                    <img src="/images/demo/bg-contact.jpg" alt="" className="h-full w-full object-cover" />
                    <div className="absolute inset-0 bg-bg-dark/90" />
                </div>

                <div className="w-full max-w-md rounded-3xl bg-bg p-8 shadow-[0_20px_60px_rgba(11,18,32,0.25)]">
                    <h1 className="text-2xl font-black text-secondary">{title}</h1>
                    <p className="mt-2 text-sm leading-[1.9] text-muted">{desc}</p>

                    <div className="mt-6">{children}</div>

                    <div className="mt-6 border-t border-gray-100 pt-5 text-center text-[13px] font-bold text-muted">
                        {footer}
                    </div>

                    <Link
                        href={`/${locale}`}
                        className="mt-4 block text-center text-[12px] font-bold text-muted transition hover:text-primary"
                    >
                        {ar ? "رجوع للموقع" : "Back to the site"}
                    </Link>
                </div>
            </section>
        </SiteLayout>
    );
}
