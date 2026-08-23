<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * بحث محفوظ + تنبيه.
 *
 * الفلاتر بتتخزّن كـ JSON زي ما هي في الرابط — نفس الشكل اللي
 * Catalog::filters بيطلّعه، فالتنبيه بيدوّر بنفس منطق الصفحة بالظبط
 * ومفيش فرصة إن اللي في الإيميل يختلف عن اللي في الموقع.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('filters');
            $table->boolean('alerts')->default(true);

            // أعلى id اتبعت عنه تنبيه — أدق من التاريخ: الوحدة اللي
            // اتنشرت واترجعت للمراجعة واتنشرت تاني متتبعتش مرتين
            $table->unsignedBigInteger('last_property_id')->default(0);
            $table->timestamp('last_alert_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'alerts']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
