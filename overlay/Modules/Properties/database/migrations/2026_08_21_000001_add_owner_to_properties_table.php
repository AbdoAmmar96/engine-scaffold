<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * صاحب الوحدة — الوسيط أو الشركة اللي ضايفها.
 * من غير العمود ده مفيش عزل: أي وسيط بيشوف عقارات أي وسيط تاني.
 * null = الوحدة بتاعة المنصّة نفسها (اللي الأدمن ضايفها).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('compound_id')
                ->constrained('users')->nullOnDelete();
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropIndex(['owner_id']);
            $table->dropColumn('owner_id');
        });
    }
};
