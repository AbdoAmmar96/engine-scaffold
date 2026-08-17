import { usePage } from "@inertiajs/react";
import { PlayCircle } from "lucide-react";
import type { SharedProps } from "@/lib/types";

/**
 * قسم الفيديو التعريفي — بيقرأ الرابط من الداشبورد (اللوجو والميديا → رابط الفيديو):
 * - رابط mp4  → مشغل فيديو مباشر
 * - رابط YouTube → embed تلقائي
 * - فاضي → بلوك جاهز باستدعاء واضح لمكان الرفع
 */
export default function BrandVideo() {
    const { settings, locale } = usePage<SharedProps>().props;
    const ar = locale === "ar";
    const branding = settings.branding ?? {};
    const url = (branding.video_url ?? "").trim();
    const poster = (branding.video_poster ?? "").trim();

    const yt = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([\w-]{11})/);

    return (
        <div className="overflow-hidden rounded-3xl border border-gray-100 bg-bg shadow-sm">
            <div className="relative aspect-video w-full bg-surface">
                {yt ? (
                    <iframe
                        src={`https://www.youtube.com/embed/${yt[1]}`}
                        title={ar ? "الفيديو التعريفي" : "Brand video"}
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowFullScreen
                        className="absolute inset-0 h-full w-full"
                    />
                ) : url ? (
                    <video controls playsInline poster={poster || undefined} className="absolute inset-0 h-full w-full object-cover">
                        <source src={url} type="video/mp4" />
                    </video>
                ) : (
                    <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 text-center">
                        <PlayCircle size={52} className="text-primary" />
                        <p className="max-w-sm px-4 text-sm leading-relaxed text-muted">
                            {ar
                                ? "مكان الفيديو التعريفي — أول ما تخلّص إنتاجه، حط الرابط من الداشبورد → اللوجو والميديا، وهيظهر هنا فورًا."
                                : "Brand video slot — once produced, paste its link in Dashboard → Logo & media and it appears here instantly."}
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
}
