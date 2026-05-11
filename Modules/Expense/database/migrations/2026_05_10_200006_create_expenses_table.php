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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paid_by_member_id')->constrained('trip_members');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('car_group_id')->nullable()->constrained('car_groups')->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 10, 2);
            $table->enum('split_type', ['equal', 'custom', 'percentage', 'per_car', 'personal'])->default('equal');
            $table->text('note')->nullable();
            $table->string('receipt_image')->nullable();
            $table->date('expense_date');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trip_id', 'split_type']);
            $table->index('paid_by_member_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
