<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PayOS\PayOS;

class PayOSServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PayOS::class, function ($app) {
            return new PayOS(
                config('services.payos.client_id'),
                config('services.payos.api_key'),
                config('services.payos.checksum_key')
            );
        });
    }
}
