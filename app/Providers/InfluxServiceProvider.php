<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use InfluxDB2\Client;

class InfluxServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Client::class, function () {
            return new Client([]);
        });
    }


}
