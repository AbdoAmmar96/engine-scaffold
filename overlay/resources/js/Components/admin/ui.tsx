import { usePage } from "@inertiajs/react";
import { useEffect, useState, type ButtonHTMLAttributes, type InputHTMLAttributes, type ReactNode } from "react";
import type { SharedProps } from "@/lib/types";

/* ---------------------------------- Button --------------------------------- */

export function Button({
    variant = "primary",
    className = "",
    ...props
}: ButtonHTMLAttributes<HTMLButtonElement> & { variant?: "primary" | "ghost" | "danger" }) {
    const styles = {
        primary: "bg-primary text-primary-fg hover:bg-primary-hover",
        ghost: "bg-transparent text-gray-700 hover:bg-gray-100 border border-gray-300",
        danger: "bg-danger text-white hover:opacity-90",
    }[variant];

    return (
        <button
            className={`inline-flex items-center justify-center gap-2 rounded-brand px-5 py-2.5 text-sm font-bold transition disabled:opacity-50 ${styles} ${className}`}
            {...props}
        />
    );
}

/* ---------------------------------- Input ---------------------------------- */

export function Field({ label, hint, error, children }: { label: string; hint?: string; error?: string; children: ReactNode }) {
    return (
        <label className="block">
            <span className="mb-1.5 block text-sm font-bold text-gray-800">{label}</span>
            {children}
            {hint && <span className="mt-1 block text-xs text-gray-400">{hint}</span>}
            {error && <span className="mt-1 block text-xs text-danger">{error}</span>}
        </label>
    );
}

export function Input({ className = "", ...props }: InputHTMLAttributes<HTMLInputElement>) {
    return (
        <input
            className={`w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/25 ${className}`}
            {...props}
        />
    );
}

/* -------------------------------- ColorField ------------------------------- */

export function ColorField({
    value,
    onChange,
}: {
    value: string;
    onChange: (v: string) => void;
}) {
    return (
        <div className="flex items-center gap-2">
            <input
                type="color"
                value={/^#[0-9a-fA-F]{6}$/.test(value) ? value : "#000000"}
                onChange={(e) => onChange(e.target.value)}
                className="h-10 w-12 cursor-pointer rounded-lg border border-gray-300 bg-white p-1"
            />
            <Input value={value} onChange={(e) => onChange(e.target.value)} dir="ltr" className="font-mono" />
        </div>
    );
}

/* ----------------------------------- Card ---------------------------------- */

export function Card({ title, actions, children }: { title?: string; actions?: ReactNode; children: ReactNode }) {
    return (
        // min-w-0 ضرورية: عنصر الجريد أو الفلكس بيرفض افتراضيًا يقل عن عرض محتواه،
        // فجدول عريض جوّه كارت كان بيمدّ الصفحة كلها بدل ما يعمل سكرول جوّه إطاره.
        // الكارت مالوش يمدّ اللي حواليه أبدًا.
        <div className="min-w-0 rounded-2xl border border-gray-200 bg-white shadow-sm">
            {(title || actions) && (
                <div className="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-4 sm:px-6">
                    <h2 className="text-base font-extrabold text-gray-900">{title}</h2>
                    {actions}
                </div>
            )}
            <div className="p-4 sm:p-6">{children}</div>
        </div>
    );
}

/* -------------------------------- FlashBanner ------------------------------ */

export function FlashBanner() {
    const { flash } = usePage<SharedProps>().props;
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        if (flash.success || flash.error) {
            setVisible(true);
            const t = setTimeout(() => setVisible(false), 3500);
            return () => clearTimeout(t);
        }
    }, [flash]);

    if (!visible || (!flash.success && !flash.error)) return null;

    return (
        <div
            className={`fixed bottom-6 start-6 z-50 rounded-xl px-5 py-3 text-sm font-bold text-white shadow-lg ${
                flash.error ? "bg-danger" : "bg-success"
            }`}
        >
            {flash.error ?? flash.success}
        </div>
    );
}
