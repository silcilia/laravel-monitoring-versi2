<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('ssl_status')->nullable();
            $table->integer('ssl_days_remaining')->nullable();
            $table->timestamp('ssl_expiry_date')->nullable();
            $table->timestamp('ssl_checked_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'ssl_status',
                'ssl_days_remaining',
                'ssl_expiry_date',
                'ssl_checked_at'
            ]);
        });
    }
};