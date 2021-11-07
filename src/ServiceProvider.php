<?php


namespace Peimengc\DouyinOpenapi;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(Api::class, function () {
            return new Api(config('services.douyin-openapi.key'), config('services.douyin-openapi.secret'));
        });

        $this->app->alias(Api::class, 'douyin-openapi');
    }

    public function provides()
    {
        return [Api::class, 'douyin-openapi'];
    }
}