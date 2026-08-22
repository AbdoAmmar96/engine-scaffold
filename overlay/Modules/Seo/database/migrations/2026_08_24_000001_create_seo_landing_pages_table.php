<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * صفحات الهبوط البرمجية: تركيبة (نوع × غرض × منطقة) بتتولّد من الوحدات
 * الموجودة فعلًا، والنصوص فيها قابلة للتحرير من اللوحة.
 *
 * الأعمدة النصية كلها nullable عن قصد: الفاضي معناه «ولّد النص»،
 * فلو الأدمن مسح تعديله بترجع للنص المولّد بدل ما تفضل فاضية.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_landing_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();

            // أبعاد الصفحة — أي واحد منهم ممكن يكون فاضي (صفحة منطقة بس مثلًا)
            $table->string('type')->nullable();
            $table->string('purpose', 10)->nullable();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();

            $table->string('h1')->nullable();
            $table->string('h1_en')->nullable();
            $table->text('intro')->nullable();
            $table->text('intro_en')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_title_en')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_description_en')->nullable();

            // عدد الوحدات وقت آخر توليد — للتقرير في اللوحة، مش للعرض
            $table->unsignedInteger('units_count')->default(0);
            $table->integer('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // التركيبة مفتاح منطقي: الأمر بيلاقي الصفحة الموجودة بدل ما يكرّرها
            $table->unique(['type', 'purpose', 'location_id'], 'seo_landing_combo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_landing_pages');
    }
};
