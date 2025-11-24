<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Tests\Laravel;

use InvalidArgumentException;
use Mahardhika\SentryResolve\Laravel\SentryResolveServiceProvider;
use Mahardhika\SentryResolve\SentryClient;
use Orchestra\Testbench\TestCase;
use Illuminate\Foundation\Application;

class SentryResolveServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SentryResolveServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('sentry-resolve.token', 'test-token');
        $app['config']->set('sentry-resolve.organization', 'test-org');
        $app['config']->set('sentry-resolve.project', 'test-proj');
    }

    public function testServiceRegistration(): void
    {
        $this->assertInstanceOf(
            SentryClient::class,
            $this->app->make(SentryClient::class)
        );
    }

    public function testServiceRegistrationFailsWhenConfigMissing(): void
    {
        $this->app['config']->set('sentry-resolve.token', null);
        $this->app['config']->set('sentry-resolve.organization', null);
        $this->app['config']->set('sentry-resolve.project', null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sentry Resolve is not configured. Please set SENTRY_TOKEN, SENTRY_ORG, and SENTRY_PROJECT, or update config/sentry-resolve.php.');

        $this->app->make(SentryClient::class);
    }

    public function testConfigMerging(): void
    {
        $config = $this->app['config']['sentry-resolve'];
        
        $this->assertEquals('test-token', $config['token']);
        $this->assertEquals('test-org', $config['organization']);
        $this->assertEquals('test-proj', $config['project']);
        $this->assertArrayHasKey('defaults', $config);
    }

    public function testCommandsRegistration(): void
    {
        $commands = $this->app->make('Illuminate\Contracts\Console\Kernel')->all();
        
        $this->assertArrayHasKey('sentry:pull', $commands);
        $this->assertArrayHasKey('sentry:resolve', $commands);
        $this->assertArrayHasKey('sentry:debug', $commands);
        $this->assertArrayHasKey('sentry:test-token', $commands);
    }
}
