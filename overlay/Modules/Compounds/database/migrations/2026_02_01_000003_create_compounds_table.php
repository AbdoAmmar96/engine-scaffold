<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compounds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->foreignId('developer_id')->nullable()->nullOnDelete()->constrained('developers');
            $table->foreignId('location_id')->nullable()->nullOnDelete()->constrained('locations');
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->string('starting_price')->nullable();
            $table->string('down_payment')->nullable();
            $table->string('installment_years')->nullable();
            $table->string('installment_years_en')->nullable();
            $table->string('delivery')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_new')->default(false);
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compounds');
    }
};
