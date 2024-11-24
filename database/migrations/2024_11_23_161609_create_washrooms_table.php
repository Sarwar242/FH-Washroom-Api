<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('washrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('floor');
            $table->enum('type', ['male', 'female', 'unisex']);
            $table->boolean('is_operational')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('washrooms');
    }
};
