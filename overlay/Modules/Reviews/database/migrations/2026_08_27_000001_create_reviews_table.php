<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * آراء العملاء.
 *
 * التقييمات أكتر محتوى بيتزوّر في المواقع العقارية، فالجدول متصمّم عشان
 * الرأي يفضل **قابل للتتبّع**: `user_id` بيقول مين كتبه فعلًا، و`source`
 * بيقول جه منين، و`property_id`/`compound_id` بيربطوه بحاجة حقيقية في
 * الكتالوج. رأي مكتوب بالإيد من غير مصدر بيفضل ممكن — بس بيبان كده في
 * اللوحة بدل ما يتلخبط مع رأي عميل حقيقي.
 *
 * ومفيش سيدر بيزرع آراء: الجدول بيبدأ فاضي والقسم بيختفي وهو فاضي.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            // مين كتبه — null يعني الأدمن كتبه بالإيد أو جه من بره
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // عن إيه — الاتنين اختياريين (رأي في الخدمة عمومًا)
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('compound_id')->nullable()->constrained()->nullOnDelete();

            $table->string('author');
            $table->string('author_en')->nullable();

            // سياق الرأي: «اشترى في كمبوند كذا» — بيدّي الرأي وزن
            $table->string('role')->nullable();
            $table->string('role_en')->nullable();

            $table->text('body');
            $table->text('body_en')->nullable();

            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('avatar')->nullable();

            // site = العميل كتبه من حسابه · manual = الأدمن كتبه · google = منقول
            $table->string('source', 12)->default('manual');

            // نفس دورة اعتماد الوحدات: مفيش رأي بيظهر قبل المراجعة
            $table->string('status', 12)->default('pending');

            $table->integer('sort')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
