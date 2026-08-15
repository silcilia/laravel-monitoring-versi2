<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            // 🔥 SEMUA KOLOM SSL WA DALAM 1 MIGRATION!
            
            // Cek dulu biar gak error duplikat
            if (!Schema::hasColumn('services', 'ssl_warning_sent_at')) {
                $table->timestamp('ssl_warning_sent_at')->nullable();
            }
            
            if (!Schema::hasColumn('services', 'ssl_critical_sent_at')) {
                $table->timestamp('ssl_critical_sent_at')->nullable();
            }
            
            if (!Schema::hasColumn('services', 'ssl_expired_sent_at')) {
                $table->timestamp('ssl_expired_sent_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'ssl_warning_sent_at',
                'ssl_critical_sent_at',
                'ssl_expired_sent_at'
            ]);
        });
    }
};