<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->enum('place_type', ['hotel', 'attraction', 'restaurant', 'activity', 'transit', 'other'])->default('other');
            $table->date('visit_date')->nullable();
            $table->time('visit_time')->nullable();
            $table->string('address')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['trip_id', 'visit_date', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_stops');
    }
};
