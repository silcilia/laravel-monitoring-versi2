<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Contract\Database;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Database::class, function ($app) {
            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase-credentials.json'))
                ->withDatabaseUri('https://laravel-monitoring-305d6-default-rtdb.asia-southeast1.firebasedatabase.app/');
            
            return $factory->createDatabase();
        });
    }

    public function boot()
    {
        //
    }
}