<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('pagespeed_score')->nullable()->after('ssl_checked_at');
            $table->float('pagespeed_lcp')->nullable()->after('pagespeed_score');
            $table->float('pagespeed_fcp')->nullable()->after('pagespeed_lcp');
            $table->float('pagespeed_tti')->nullable()->after('pagespeed_fcp');
            $table->float('pagespeed_cls')->nullable()->after('pagespeed_tti');
            $table->timestamp('pagespeed_checked_at')->nullable()->after('pagespeed_cls');
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'pagespeed_score',
                'pagespeed_lcp',
                'pagespeed_fcp',
                'pagespeed_tti',
                'pagespeed_cls',
                'pagespeed_checked_at'
            ]);
        });
    }
};