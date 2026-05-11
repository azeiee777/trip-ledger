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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('upi_id')->nullable()->after('phone');
            $table->string('avatar')->nullable()->after('upi_id');
            $table->string('google_id')->nullable()->unique()->after('avatar');
            $table->string('password')->nullable()->change();
            $table->softDeletes()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'upi_id', 'avatar', 'google_id', 'deleted_at']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
