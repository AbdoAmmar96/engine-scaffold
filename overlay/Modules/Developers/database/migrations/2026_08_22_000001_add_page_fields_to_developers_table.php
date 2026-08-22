<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** المطوّر بقى ليه صفحة عرض، فمحتاج رابط ثابت وغلاف وبيانات تعريفية */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('developers', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name_en');
            $table->string('cover')->nullable()->after('logo');       // خلفية الهيرو
            $table->string('website')->nullable()->after('cover');
            $table->string('founded_year')->nullable()->after('website');
            $table->string('headquarters')->nullable()->after('founded_year');
            $table->string('headquarters_en')->nullable()->after('headquarters');
        });

        foreach (DB::table('developers')->select('id', 'name', 'name_en')->get() as $row) {
            DB::table('developers')->where('id', $row->id)->update([
                'slug' => self::uniqueSlug($row->name_en, $row->name, $row->id),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('developers', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'cover', 'website', 'founded_year', 'headquarters', 'headquarters_en']);
        });
    }

    private static function uniqueSlug(?string $en, ?string $ar, int $id): string
    {
        $base = filled($en) ? Str::slug($en) : Str::slug((string) $ar, '-', null);
        $base = $base !== '' ? $base : 'developer';
        $slug = $base;
        $i = 2;

        while (DB::table('developers')->where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
};
