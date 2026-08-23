<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * مكتبة الميديا — ملفات على قرص public تحت media/.
 * مفيش جدول: القايمة بتتقرا من الملفات نفسها، فأي ملف بيتحط يدوي بيظهر برضه.
 * الشاشة والـ MediaPicker الاتنين بيستهلكوا نفس الـ JSON endpoints.
 */
class MediaController extends Controller
{
    private const DIR = 'media';

    private const IMAGE_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'svg'];

    private const VIDEO_EXT = ['mp4', 'webm', 'ogg'];

    public function index(): Response
    {
        return Inertia::render('Admin/Media/Index');
    }

    /** قايمة الملفات — الأحدث الأول */
    public function list(): JsonResponse
    {
        $disk = Storage::disk('public');

        if (! $disk->exists(self::DIR)) {
            $disk->makeDirectory(self::DIR);
        }

        $files = collect($disk->files(self::DIR))
            ->filter(fn ($p) => in_array($this->ext($p), [...self::IMAGE_EXT, ...self::VIDEO_EXT], true))
            ->map(fn ($p) => [
                'path' => '/storage/'.$p,
                'name' => basename($p),
                'type' => in_array($this->ext($p), self::VIDEO_EXT, true) ? 'video' : 'image',
                'size' => $this->human($disk->size($p)),
                'time' => $disk->lastModified($p),
            ])
            ->sortByDesc('time')
            ->values();

        return response()->json(['files' => $files]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:51200', // 50 ميجا — الفيديوهات محتاجة مساحة
                'mimes:'.implode(',', [...self::IMAGE_EXT, ...self::VIDEO_EXT]),
            ],
        ], [], ['file' => 'الملف']);

        $file = $request->file('file');

        // اسم نظيف + لاحقة عشوائية بدل ما ملف يمسح ملف
        $base = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), '-', null);
        $base = $base !== '' ? Str::limit($base, 60, '') : 'file';
        $name = $base.'-'.Str::lower(Str::random(5)).'.'.Str::lower($file->getClientOriginalExtension());

        $file->storeAs(self::DIR, $name, 'public');

        return response()->json([
            'file' => [
                'path' => '/storage/'.self::DIR.'/'.$name,
                'name' => $name,
                'type' => in_array(Str::lower($file->getClientOriginalExtension()), self::VIDEO_EXT, true) ? 'video' : 'image',
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['path' => ['required', 'string']]);

        // مسموح بالمسح جوه media/ بس — مفيش خروج بره الفولدر
        $relative = ltrim(Str::after($data['path'], '/storage/'), '/');

        abort_unless(Str::startsWith($relative, self::DIR.'/') && ! Str::contains($relative, '..'), 422);

        Storage::disk('public')->delete($relative);

        return response()->json(['ok' => true]);
    }

    private function ext(string $path): string
    {
        return Str::lower(pathinfo($path, PATHINFO_EXTENSION));
    }

    private function human(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        return max(1, (int) round($bytes / 1024)).' KB';
    }
}
