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
        Schema::table('services', function (Blueprint $table) {
            // Tambahkan kolom consecutive_failures untuk menghitung kegagalan berturut-turut
            $table->integer('consecutive_failures')->default(0)->after('last_check_at');
            
            // Tambahkan kolom last_failure_at (opsional) untuk tracking waktu kegagalan terakhir
            $table->timestamp('last_failure_at')->nullable()->after('consecutive_failures');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['consecutive_failures', 'last_failure_at']);
        });
    }
};