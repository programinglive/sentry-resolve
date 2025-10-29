<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Laravel;

use Illuminate\Support\ServiceProvider;
use Mahardhika\SentryResolve\SentryClient;
use Mahardhika\SentryResolve\Commands\SentryPullCommand;
use Mahardhika\SentryResolve\Commands\SentryResolveCommand;
use Mahardhika\SentryResolve\Commands\SentryDebugCommand;
use Mahardhika\SentryResolve\Commands\SentryTestTokenCommand;

class SentryResolveServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sentry-resolve.php',
            'sentry-resolve'
        );

        $this->app->singleton(SentryClient::class, function ($app) {
            $config = $app['config']['sentry-resolve'];
            
            return new SentryClient(
                $config['token'],
                $config['organization'],
                $config['project']
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sentry-resolve.php' => config_path('sentry-resolve.php'),
            ], 'sentry-resolve-config');

            $this->commands([
                SentryPullCommand::class,
                SentryResolveCommand::class,
                SentryDebugCommand::class,
                SentryTestTokenCommand::class,
            ]);
        }
    }
}
