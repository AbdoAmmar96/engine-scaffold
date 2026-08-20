<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الطلب بقى ليه طرفين:
 *   user_id  ← العميل اللي بعته (لو كان مسجّل دخول) — بيظهر في «طلباتي»
 *   owner_id ← الوسيط/الشركة اللي الطلب من حقه — بيتحدد من الوحدة المطلوبة
 * وبنسجّل الوحدة/المشروع نفسه عشان اللي بيرد يعرف العميل بيسأل على إيه.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->after('owner_id')->constrained('properties')->nullOnDelete();
            $table->foreignId('compound_id')->nullable()->after('property_id')->constrained('compounds')->nullOnDelete();

            $table->index('owner_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            foreach (['user_id', 'owner_id', 'property_id', 'compound_id'] as $col) {
                $table->dropForeign([$col]);
            }

            $table->dropIndex(['owner_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn(['user_id', 'owner_id', 'property_id', 'compound_id']);
        });
    }
};
