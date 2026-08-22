<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * الحقول اللي الفلاتر والمراجعة بيقوموا عليها.
 *
 * أهم حاجة هنا price_amount: السعر كان متخزّن كنص منسّق ("EGP 4,850,000")،
 * والنص ده مينفعش تفلتر بيه ولا ترتّب عليه. الرقم بقى هو المصدر،
 * و price/price_en بقوا نص اختياري بيغلب العرض بس (زي "السعر عند الاستعلام").
 *
 * status = حالة المراجعة · is_active = «معروض على الموقع» من صاحبه.
 * الاتنين لازم يبقوا صح عشان الوحدة تبان — scopePublished في الموديل.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // --- الأرقام اللي الفلاتر بتشتغل عليها ---
            $table->unsignedBigInteger('price_amount')->nullable()->after('type');
            $table->unsignedBigInteger('down_payment')->nullable()->after('price_en');
            $table->unsignedBigInteger('monthly_installment')->nullable()->after('down_payment');
            $table->unsignedTinyInteger('installment_years')->nullable()->after('monthly_installment');
            $table->unsignedSmallInteger('delivery_year')->nullable()->after('installment_years');

            // --- مواصفات ---
            $table->string('finishing')->nullable()->after('size');     // none/semi/full/furnished/flexi
            $table->string('floor')->nullable()->after('finishing');
            $table->boolean('has_garden')->default(false)->after('floor');
            $table->boolean('has_roof')->default(false)->after('has_garden');
            $table->boolean('has_dressing_room')->default(false)->after('has_roof');

            // --- المراجعة والعرض ---
            $table->string('status')->default('published')->after('is_active');
            $table->text('rejection_reason')->nullable()->after('status');
            $table->timestamp('published_at')->nullable()->after('rejection_reason');
            $table->boolean('is_featured')->default(false)->after('published_at');
            $table->unsignedInteger('views_count')->default(0)->after('is_featured');

            $table->index('status');
            $table->index('is_featured');
            $table->index('price_amount');
            // فهرس مركّب لصفحات الفلترة — أكتر تركيبة بتتسأل
            $table->index(['type', 'purpose', 'location_id']);
        });

        // السعر المنسّق → رقم. "EGP 38,000 / شهريًا" بترجّع 38000.
        foreach (DB::table('properties')->select('id', 'price', 'is_active')->get() as $row) {
            $digits = preg_replace('/\D+/', '', (string) $row->price) ?? '';

            DB::table('properties')->where('id', $row->id)->update([
                'price_amount' => $digits === '' ? null : (int) $digits,
                // الموجود دلوقتي كله معتمد — المراجعة بتبدأ من الوحدات الجديدة
                'status' => $row->is_active ? 'published' : 'draft',
                'published_at' => $row->is_active ? now() : null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['price_amount']);
            $table->dropIndex(['type', 'purpose', 'location_id']);

            $table->dropColumn([
                'price_amount', 'down_payment', 'monthly_installment', 'installment_years',
                'delivery_year', 'finishing', 'floor', 'has_garden', 'has_roof', 'has_dressing_room',
                'status', 'rejection_reason', 'published_at', 'is_featured', 'views_count',
            ]);
        });
    }
};
