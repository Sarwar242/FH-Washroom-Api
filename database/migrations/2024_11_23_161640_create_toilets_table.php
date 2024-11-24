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
        Schema::create('toilets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('washroom_id')->constrained()->onDelete('cascade');
            $table->string('number');  // Like T1, T2, etc.
            $table->boolean('is_occupied')->default(false);
            $table->foreignId('occupied_by')->nullable()->constrained('users');
            $table->timestamp('occupied_at')->nullable();
            $table->timestamp('occupation_expires_at')->nullable();
            $table->boolean('is_operational')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('toilets');
    }
};
