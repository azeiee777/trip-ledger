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
        Schema::create('expense_splits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trip_member_id')->constrained('trip_members')->cascadeOnDelete();
            $table->decimal('share_amount', 10, 2);
            $table->decimal('share_percentage', 5, 2)->nullable();
            $table->boolean('is_excluded')->default(false);
            $table->timestamps();

            $table->index('expense_id');
            $table->index('trip_member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_splits');
    }
};
