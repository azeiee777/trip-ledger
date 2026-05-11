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
        Schema::table('trip_members', function (Blueprint $table) {
            // Role: admin = trip owner, member = regular participant
            $table->enum('role', ['admin', 'member'])->default('member')->after('is_active');

            // Invite flow
            $table->string('invite_email')->nullable()->after('guest_phone');
            $table->enum('invite_status', ['pending', 'accepted', 'declined'])->default('accepted')->after('role');
            $table->timestamp('invite_sent_at')->nullable()->after('invite_status');
            $table->timestamp('invite_accepted_at')->nullable()->after('invite_sent_at');
            $table->timestamp('invite_token_expires_at')->nullable()->after('invite_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('trip_members', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'invite_email', 'invite_status',
                'invite_sent_at', 'invite_accepted_at', 'invite_token_expires_at',
            ]);
        });
    }
};
