<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * المنطقة بقى ليها صفحة: رابط ثابت + نبذة طويلة + غلاف.
 * note الموجودة قصيرة وبتتعرض على الكارت، فـ about للصفحة نفسها.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name_en');
            $table->text('about')->nullable()->after('note_en');
            $table->text('about_en')->nullable()->after('about');
            $table->string('cover')->nullable()->after('image');
            $table->boolean('is_featured')->default(false)->after('cover');
        });

        foreach (DB::table('locations')->select('id', 'name', 'name_en')->get() as $row) {
            DB::table('locations')->where('id', $row->id)->update([
                'slug' => self::uniqueSlug($row->name_en, $row->name, $row->id),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'about', 'about_en', 'cover', 'is_featured']);
        });
    }

    private static function uniqueSlug(?string $en, ?string $ar, int $id): string
    {
        $base = filled($en) ? Str::slug($en) : Str::slug((string) $ar, '-', null);
        $base = $base !== '' ? $base : 'area';
        $slug = $base;
        $i = 2;

        while (DB::table('locations')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
};
