<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الحساب بقى فيه أنواع: عميل بيسجّل من الموقع، ووسيط، وشركة.
 * الموبايل مطلوب للعميل (وسيلة التواصل الحقيقية في السوق ده)،
 * و company_name بيظهر بدل الاسم الشخصي لحسابات الشركات.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('company_name')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('company_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'company_name', 'is_active']);
        });
    }
};
