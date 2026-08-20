<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->foreignId('location_id')->nullable()->nullOnDelete()->constrained('locations');
            $table->foreignId('compound_id')->nullable()->nullOnDelete()->constrained('compounds');
            $table->string('purpose')->default('sale');   // sale | rent
            $table->string('type')->nullable();           // شقة / فيلا / ...
            $table->string('price')->nullable();
            $table->string('price_en')->nullable();
            $table->unsignedInteger('beds')->default(0);
            $table->unsignedInteger('baths')->default(0);
            $table->unsignedInteger('size')->default(0);
            $table->string('ref')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
