<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سجل النشاط — مين عمل إيه وإمتى.
 *
 * بيتسجّل فيه أفعال الناس بس (اللي فيها مستخدم مسجّل دخوله). السيدرز
 * والأوامر مبتتسجّلش: السجل ده للمساءلة، ولو دخل فيه كل حاجة بيتحوّل
 * لضوضاء محدش بيفتحها.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_label');
            $table->string('action', 20);
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable();

            // أسماء الحقول اللي اتغيّرت بس — مش قيمها.
            // القيم كانت هتودّي هاشات كلمات المرور وبيانات العملاء لجدول
            // بيتفتح من اللوحة.
            $table->json('changed')->nullable();

            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['subject_type', 'subject_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
