<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * «شوهدت مؤخرًا» للحساب المسجّل.
 *
 * الزائر بيتخزّنله في localStorage — الجدول ده للمسجّل بس، عشان القايمة
 * تمشي معاه بين الموبايل والكمبيوتر.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->timestamp('viewed_at');

            // زيارة تانية بتحدّث الوقت مش بتضيف صف
            $table->unique(['user_id', 'property_id']);
            $table->index(['user_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recently_viewed');
    }
};
