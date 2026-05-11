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
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payer_member_id')->constrained('trip_members');
            $table->foreignId('receiver_member_id')->constrained('trip_members');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'paid', 'partial'])->default('pending');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->enum('payment_method', ['upi', 'cash', 'bank'])->nullable();
            $table->text('payment_note')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->index(['trip_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
