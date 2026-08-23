<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * صفحات المحتوى الثابت: سياسة الخصوصية · الشروط · الأسئلة الشائعة … إلخ.
 *
 * بتعيش على `/{locale}/{slug}` مباشرة — رابط نضيف من غير بادئة زي `/pages/`.
 * الثمن إن مساحة الأسماء دي متشاركة مع كل راوت من مقطع واحد في الموقع،
 * فالتحقق بيرفض أي slug بيصطدم بمسار موجود (App\Support\ReservedSlugs).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            $table->string('title');
            $table->string('title_en')->nullable();

            // سطر تحت العنوان — بيبان في الهيرو وبيتستخدم كوصف احتياطي للميتا
            $table->text('excerpt')->nullable();
            $table->text('excerpt_en')->nullable();

            $table->longText('body')->nullable();
            $table->longText('body_en')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_description_en')->nullable();

            // صفحة زي «تم استلام طلبك» مالهاش لازمة في نتايج البحث.
            // منفصلة عن is_active عن قصد: منشورة ومفهرسة قرارين مختلفين.
            $table->boolean('is_indexable')->default(true);

            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
