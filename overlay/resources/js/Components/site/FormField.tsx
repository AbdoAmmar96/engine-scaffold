import type { ReactNode } from "react";

/** حقل فورم للموقع العام — نفس شكل حقول «اتصل بنا» */
export default function FormField({
    label,
    error,
    hint,
    children,
}: {
    label: string;
    error?: string;
    hint?: string;
    children: ReactNode;
}) {
    return (
        <label className="flex flex-col gap-1.5">
            <span className="text-[13px] font-extrabold text-secondary">{label}</span>
            {children}
            {error ? (
                <span className="text-[12px] font-bold text-danger">{error}</span>
            ) : (
                hint && <span className="text-[11px] text-muted">{hint}</span>
            )}
        </label>
    );
}

export const inputClass =
    "w-full rounded-xl border border-gray-200 bg-bg px-4 py-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/20";
