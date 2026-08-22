<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * مطوّر الوحدة.
 *
 * قبل العمود ده المطوّر كان بيتوصل له بقفزتين (property → compound → developer)،
 * فأي وحدة مش جوه كمبوند — إعادة بيع، وحدة مستقلة، أي حاجة وسيط بينشرها بنفسه —
 * مكانش ليها مطوّر خالص، والبحث بالمطوّر في تاب العقارات كان بيرجّع صفر.
 *
 * الكمبوند فضل fallback: العمود ده null معناه «خُد مطوّر الكمبوند»،
 * فالوحدات جوه المشاريع مش محتاجة إدخال مزدوج.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('developer_id')->nullable()->after('compound_id')
                ->constrained('developers')->nullOnDelete();
            $table->index('developer_id');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['developer_id']);
            $table->dropIndex(['developer_id']);
            $table->dropColumn('developer_id');
        });
    }
};
