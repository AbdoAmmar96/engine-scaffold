<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * قوائم منسدلة: العنصر ممكن يبقى تحت عنصر تاني.
 * مستوى واحد بس — قايمة عقارات مش محتاجة أكتر من كده،
 * والأعمق من كده بيبوظ التنقّل على الموبايل.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('location')
                ->constrained('menu_items')->cascadeOnDelete();

            // العنصر الأب ممكن يبقى عنوان بس من غير صفحة
            $table->string('url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
