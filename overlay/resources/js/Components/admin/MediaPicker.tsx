import { Loader2, Trash2, Upload, X } from "lucide-react";
import { useCallback, useEffect, useRef, useState } from "react";
import { deleteMedia, listMedia, mediaError, uploadMedia, type MediaFile } from "@/lib/media";

/**
 * شبكة الميديا المشتركة — بتستخدمها شاشة المكتبة والمنتقي في الفورمات.
 * الرفع بالسحب والإفلات أو بالزرار، والحذف بتأكيد.
 */
export function MediaGrid({
    onPick,
    selectable = false,
    current,
}: {
    onPick?: (file: MediaFile) => void;
    selectable?: boolean;
    current?: string;
}) {
    const [files, setFiles] = useState<MediaFile[] | null>(null);
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [dragging, setDragging] = useState(false);
    const input = useRef<HTMLInputElement>(null);

    const load = useCallback(async () => {
        try {
            setFiles(await listMedia());
        } catch (e) {
            setError(mediaError(e));
            setFiles([]);
        }
    }, []);

    useEffect(() => {
        void load();
    }, [load]);

    const upload = async (list: FileList | null) => {
        if (!list?.length) return;

        setBusy(true);
        setError(null);

        try {
            for (const file of Array.from(list)) await uploadMedia(file);
            await load();
        } catch (e) {
            setError(mediaError(e));
        } finally {
            setBusy(false);
            if (input.current) input.current.value = "";
        }
    };

    const remove = async (file: MediaFile) => {
        if (!confirm(`متأكد من حذف "${file.name}"؟ أي مكان يستخدم هذا الملف سيصبح فارغًا.`)) return;

        setBusy(true);

        try {
            await deleteMedia(file.path);
            await load();
        } catch (e) {
            setError(mediaError(e));
        } finally {
            setBusy(false);
        }
    };

    return (
        <div className="flex flex-col gap-4">
            <div
                onDragOver={(e) => {
                    e.preventDefault();
                    setDragging(true);
                }}
                onDragLeave={() => setDragging(false)}
                onDrop={(e) => {
                    e.preventDefault();
                    setDragging(false);
                    void upload(e.dataTransfer.files);
                }}
                className={`flex flex-col items-center gap-2 rounded-2xl border-2 border-dashed p-6 text-center transition ${
                    dragging ? "border-primary bg-primary/5" : "border-gray-200"
                }`}
            >
                <input
                    ref={input}
                    type="file"
                    multiple
                    accept="image/*,video/mp4,video/webm"
                    onChange={(e) => void upload(e.target.files)}
                    className="hidden"
                />

                <button
                    type="button"
                    onClick={() => input.current?.click()}
                    disabled={busy}
                    className="inline-flex items-center gap-2 rounded-brand bg-primary px-5 py-2.5 text-sm font-bold text-primary-fg transition hover:bg-primary-hover disabled:opacity-50"
                >
                    {busy ? <Loader2 size={16} className="animate-spin" /> : <Upload size={16} />}
                    {busy ? "جارٍ الرفع…" : "ارفع ملفات"}
                </button>

                <span className="text-xs text-gray-400">أو اسحب الملفات هنا · صور وفيديو حتى 50 ميجا</span>
            </div>

            {error && <p className="rounded-xl bg-danger/10 px-4 py-2.5 text-sm font-bold text-danger">{error}</p>}

            {files === null ? (
                <p className="py-10 text-center text-sm text-gray-400">جارٍ التحميل…</p>
            ) : files.length === 0 ? (
                <p className="rounded-2xl border border-dashed border-gray-200 py-10 text-center text-sm text-gray-400">
                    المكتبة فارغة — ارفع أول ملف.
                </p>
            ) : (
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    {files.map((file) => {
                        const active = current === file.path;

                        return (
                            <div
                                key={file.path}
                                className={`group relative overflow-hidden rounded-xl border bg-surface transition ${
                                    active ? "border-primary ring-2 ring-primary/30" : "border-gray-200 hover:border-primary/50"
                                }`}
                            >
                                <button
                                    type="button"
                                    onClick={() => onPick?.(file)}
                                    disabled={!selectable}
                                    className="block w-full text-start disabled:cursor-default"
                                >
                                    <span className="block h-28 w-full bg-gray-100">
                                        {file.type === "video" ? (
                                            <video src={file.path} muted className="h-full w-full object-cover" />
                                        ) : (
                                            <img src={file.path} alt="" loading="lazy" className="h-full w-full object-cover" />
                                        )}
                                    </span>

                                    <span className="block px-2.5 py-2">
                                        <span className="block truncate text-[11px] font-bold text-gray-700" dir="ltr">
                                            {file.name}
                                        </span>
                                        <span className="block text-[10px] text-gray-400" dir="ltr">
                                            {file.size} · {file.type === "video" ? "فيديو" : "صورة"}
                                        </span>
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    onClick={() => void remove(file)}
                                    className="absolute end-1.5 top-1.5 rounded-lg bg-white/90 p-1.5 text-gray-400 opacity-0 shadow-sm transition hover:text-danger group-hover:opacity-100"
                                    aria-label="حذف"
                                >
                                    <Trash2 size={14} />
                                </button>
                            </div>
                        );
                    })}
                </div>
            )}
        </div>
    );
}

/** نافذة اختيار ملف من المكتبة */
export default function MediaPicker({
    open,
    current,
    multiple = false,
    onClose,
    onPick,
}: {
    open: boolean;
    current?: string;
    /** معرض صور: المنتقي بيفضل مفتوح عشان تختار أكتر من صورة */
    multiple?: boolean;
    onClose: () => void;
    onPick: (path: string) => void;
}) {
    useEffect(() => {
        if (!open) return;

        const onKey = (e: KeyboardEvent) => e.key === "Escape" && onClose();
        document.addEventListener("keydown", onKey);

        return () => document.removeEventListener("keydown", onKey);
    }, [open, onClose]);

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4 py-10">
            <div className="w-full max-w-4xl rounded-2xl bg-white shadow-xl">
                <div className="flex items-center justify-between border-b border-gray-100 px-6 py-4">
                    <h2 className="text-base font-extrabold text-gray-900">
                        {multiple ? "اختر صورًا من المكتبة — يمكنك اختيار أكثر من واحدة" : "اختر من مكتبة الميديا"}
                    </h2>
                    <button
                        type="button"
                        onClick={onClose}
                        className="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                        aria-label="إغلاق"
                    >
                        <X size={18} />
                    </button>
                </div>

                <div className="p-6">
                    <MediaGrid
                        selectable
                        current={current}
                        onPick={(file) => {
                            onPick(file.path);
                            if (!multiple) onClose();
                        }}
                    />
                </div>
            </div>
        </div>
    );
}
