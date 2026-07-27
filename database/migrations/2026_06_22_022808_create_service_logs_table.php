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
        Schema::create('service_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('status');

            $table->string('response_code')
                ->nullable();

            $table->float('response_time')
                ->nullable();

            $table->text('message')
                ->nullable();

            // Menandakan apakah log ini merupakan perubahan status
            $table->boolean('is_status_change')
                ->default(false)
                ->comment('Menandakan apakah ini adalah perubahan status');

            // Menyimpan status sebelumnya
            $table->string('previous_status')
                ->nullable()
                ->comment('Status sebelumnya sebelum perubahan');

            // Index
            $table->index('is_status_change');
            $table->index(['service_id', 'is_status_change']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_logs');
    }
};