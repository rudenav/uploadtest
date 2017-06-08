<?php

namespace App\Providers;

use App\Http\Models\RemoteSiteModel;
use GuzzleHttp\Cookie\FileCookieJar;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(RemoteSiteModel::class, function ($app) {
            $client = app('guzzle');
            return new RemoteSiteModel($client);
        });
    }
}
