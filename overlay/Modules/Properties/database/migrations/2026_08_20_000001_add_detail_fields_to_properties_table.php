<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * صفحة تفاصيل العقار محتاجة أكتر من بيانات الكارت:
 * رابط ثابت (slug) · وصف · مميزات · صور إضافية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title_en');
            $table->text('description')->nullable()->after('type');
            $table->text('description_en')->nullable()->after('description');
            $table->text('features')->nullable()->after('description_en');       // بند في كل سطر
            $table->text('features_en')->nullable()->after('features');
            $table->text('gallery')->nullable()->after('image');                 // مسار صورة في كل سطر
        });

        // الصفوف الموجودة قبل العمود لازم تاخد رابط، وإلا صفحتها 404
        foreach (DB::table('properties')->select('id', 'title', 'title_en', 'ref')->get() as $row) {
            DB::table('properties')->where('id', $row->id)->update([
                'slug' => self::uniqueSlug($row->title_en, $row->title, $row->ref, $row->id),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'description', 'description_en', 'features', 'features_en', 'gallery']);
        });
    }

    private static function uniqueSlug(?string $en, ?string $ar, ?string $ref, int $id): string
    {
        $base = filled($en) ? Str::slug($en) : Str::slug((string) $ar, '-', null);
        $base = $base !== '' ? $base : (Str::slug((string) $ref) ?: 'property');
        $slug = $base;
        $i = 2;

        while (DB::table('properties')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
};
