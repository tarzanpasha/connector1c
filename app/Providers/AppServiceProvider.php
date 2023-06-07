<?php

namespace App\Providers;

use App\Domain\External\ExportTo1c\Api\ExportTo1cApi;
use App\Domain\External\ExportTo1c\Client;
use App\Domain\External\ExportTo1c\Configuration;
use App\Domain\External\ExportTo1c\Services\DataCreatorService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Date;
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
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::preventLazyLoading(!app()->isProduction());
        Date::use(CarbonImmutable::class);
        $this->make1cApiConfigs();
    }

    private function make1cApiConfigs(): void
    {
        $this->app->bind(Configuration::class, function (Application $app) {
            return new Configuration(config('services.1c.url'), config('services.1c.auth'));
        });
        $this->app->bind(Client::class, function (Application $app) {
            return new Client($app->make(Configuration::class));
        });
        $this->app->bind(ExportTo1cApi::class, function (Application $app) {
            return new ExportTo1cApi($app->make(Client::class), $app->make(DataCreatorService::class));
        });
    }
}
