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
            // 🔥 Tambahkan kolom is_archived (default 0 = tidak diarsip)
            $table->boolean('is_archived')->default(0)->after('wa_interval_minutes');
            
            // 🔥 Tambahkan kolom archived_at (waktu diarsipkan)
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            
            // 🔥 Tambahkan index untuk optimasi query
            $table->index('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['is_archived', 'archived_at']);
        });
    }
};