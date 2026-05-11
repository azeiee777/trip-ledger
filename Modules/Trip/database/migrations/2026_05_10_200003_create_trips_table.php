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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('destination')->nullable();
            $table->text('description')->nullable();
            $table->enum('trip_type', ['road_trip', 'flight', 'local', 'international', 'pilgrimage', 'family'])->default('road_trip');
            $table->enum('status', ['upcoming', 'ongoing', 'completed', 'archived'])->default('upcoming');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('total_spend', 10, 2)->default(0);
            $table->unsignedInteger('member_count')->default(0);
            $table->string('invite_token', 64)->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
