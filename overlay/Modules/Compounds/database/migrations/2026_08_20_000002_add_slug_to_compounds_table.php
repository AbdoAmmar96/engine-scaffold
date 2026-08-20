<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** رابط ثابت لصفحة تفاصيل الكمبوند + مميزات وصور إضافية */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compounds', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name_en');
            $table->text('features')->nullable()->after('description_en');   // بند في كل سطر
            $table->text('features_en')->nullable()->after('features');
            $table->text('gallery')->nullable()->after('image');             // مسار صورة في كل سطر
        });

        foreach (DB::table('compounds')->select('id', 'name', 'name_en')->get() as $row) {
            DB::table('compounds')->where('id', $row->id)->update([
                'slug' => self::uniqueSlug($row->name_en, $row->name, $row->id),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('compounds', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'features', 'features_en', 'gallery']);
        });
    }

    private static function uniqueSlug(?string $en, ?string $ar, int $id): string
    {
        $base = filled($en) ? Str::slug($en) : Str::slug((string) $ar, '-', null);
        $base = $base !== '' ? $base : 'compound';
        $slug = $base;
        $i = 2;

        while (DB::table('compounds')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
};
