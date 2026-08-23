import axios from "axios";

export interface MediaFile {
    path: string;
    name: string;
    type: "image" | "video";
    size?: string;
    time?: number;
}

/** axios بيبعت XSRF-TOKEN من الكوكي لوحده، فمفيش تعامل يدوي مع CSRF */
export async function listMedia(): Promise<MediaFile[]> {
    const { data } = await axios.get<{ files: MediaFile[] }>("/admin/media/files");
    return data.files;
}

export async function uploadMedia(file: File): Promise<MediaFile> {
    const body = new FormData();
    body.append("file", file);

    const { data } = await axios.post<{ file: MediaFile }>("/admin/media/files", body);
    return data.file;
}

export async function deleteMedia(path: string): Promise<void> {
    await axios.delete("/admin/media/files", { data: { path } });
}

/** رسالة الخطأ اللي لارافيل رجّعها، أو رسالة عامة */
export function mediaError(e: unknown): string {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } };
    const errors = err.response?.data?.errors;

    if (errors) return Object.values(errors)[0]?.[0] ?? "فشل الرفع";

    return err.response?.data?.message ?? "فشل الرفع — حاول مرة أخرى";
}

export const isVideo = (path: string) => /\.(mp4|webm|ogg)$/i.test(path);
