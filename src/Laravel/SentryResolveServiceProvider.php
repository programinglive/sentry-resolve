<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Laravel;

use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Mahardhika\SentryResolve\SentryClient;
use Mahardhika\SentryResolve\Commands\SentryPullCommand;
use Mahardhika\SentryResolve\Commands\SentryResolveCommand;
use Mahardhika\SentryResolve\Commands\SentryDebugCommand;
use Mahardhika\SentryResolve\Commands\SentryTestTokenCommand;
use Mahardhika\SentryResolve\Logging\ResolutionLogger;

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

            $token = $config['token'] ?? null;
            $organization = $config['organization'] ?? null;
            $project = $config['project'] ?? null;

            if (!$token || !$organization || !$project) {
                throw new InvalidArgumentException(
                    'Sentry Resolve is not configured. Please set SENTRY_TOKEN, SENTRY_ORG, and SENTRY_PROJECT, or update config/sentry-resolve.php.'
                );
            }

            return new SentryClient(
                (string) $token,
                (string) $organization,
                (string) $project
            );
        });

        $this->app->singleton('sentry-resolve.logger', function ($app) {
            $config = $app['config']['sentry-resolve']['logging'] ?? [];

            $enabled = $config['enabled'] ?? true;
            if (is_string($enabled)) {
                $enabled = !in_array(strtolower($enabled), ['0', 'false', 'off', 'no'], true);
            } else {
                $enabled = (bool) $enabled;
            }

            if (!$enabled) {
                return null;
            }

            $path = (string) ($config['path'] ?? (function_exists('storage_path') ? storage_path('logs') : dirname(__DIR__, 2) . '/storage/logs'));
            $frequency = (string) ($config['frequency'] ?? 'daily');
            $prefix = (string) ($config['prefix'] ?? 'sentry-resolve');

            return new ResolutionLogger($path, $frequency, $prefix);
        });

        $this->app->singleton(SentryResolveCommand::class, function ($app) {
            $client = $app->make(SentryClient::class);
            $logger = $app->make('sentry-resolve.logger');

            return new SentryResolveCommand($client, $logger);
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
