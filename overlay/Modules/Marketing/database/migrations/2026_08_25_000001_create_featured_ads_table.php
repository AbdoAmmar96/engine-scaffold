<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المساحات الإعلانية المجدولة.
 *
 * مستقلة عن `properties.is_featured`: الفلاج ده «إعلان مميّز دائم»
 * وبيتصدّر نتايج البحث، أما ده فمساحة في مكان محدّد لفترة محدّدة —
 * تبدأ وتنتهي لوحدها من غير ما حد يفتكر يقفلها.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('featured_ads', function (Blueprint $table) {
            $table->id();
            $table->string('position', 20);

            // الإعلان على وحدة أو على مشروع — واحد منهم بس
            $table->foreignId('property_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('compound_id')->nullable()->constrained()->cascadeOnDelete();

            // مين طلبه — فاضي يعني قرار داخلي مش طلب من معلن
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('approved');
            $table->string('rejection_reason')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);

            // أداء المساحة — بيتقاسوا من العرض والضغط الفعليين
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);

            $table->timestamps();

            $table->index(['position', 'status', 'is_active']);
            $table->index(['starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_ads');
    }
};
