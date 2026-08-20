<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** صاحب المشروع — الشركة المطوّرة اللي ليها حساب على المنصّة */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compounds', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('developer_id')
                ->constrained('users')->nullOnDelete();
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::table('compounds', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropIndex(['owner_id']);
            $table->dropColumn('owner_id');
        });
    }
};
