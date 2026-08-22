<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فهارس صفحات الهبوط والفلاتر. كل صفحة هبوط استعلامها
 * (status, is_active) + (type, purpose, location_id) وترتيب على السعر —
 * من غير فهرس ده full scan على كل زيارة من جوجل.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->index(['status', 'is_active'], 'properties_visibility_index');
            $table->index(['type', 'purpose', 'location_id'], 'properties_combo_index');
            $table->index('price_amount', 'properties_price_index');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropIndex('properties_visibility_index');
            $table->dropIndex('properties_combo_index');
            $table->dropIndex('properties_price_index');
        });
    }
};
