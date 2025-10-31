<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Tests;

use Mahardhika\SentryResolve\Bootstrap\SentryBootstrapper;
use PHPUnit\Framework\TestCase;

class SentryResolveCliTest extends TestCase
{
    public function testBootstrapInitializesSentryWhenDsnProvided(): void
    {
        $capturedOptions = null;
        $warningMessages = [];

        SentryBootstrapper::bootstrap('https://examplePublicKey@o0.ingest.sentry.io/0', static function (array $options) use (&$capturedOptions): void {
            $capturedOptions = $options;
        }, static function (string $message) use (&$warningMessages): void {
            $warningMessages[] = $message;
        });

        $this->assertSame(['dsn' => 'https://examplePublicKey@o0.ingest.sentry.io/0'], $capturedOptions);
        $this->assertSame([], $warningMessages);
    }

    public function testBootstrapWarnsWhenDsnMissing(): void
    {
        $capturedOptions = [];
        $warningMessages = [];

        SentryBootstrapper::bootstrap(null, static function (array $options) use (&$capturedOptions): void {
            $capturedOptions[] = $options;
        }, static function (string $message) use (&$warningMessages): void {
            $warningMessages[] = $message;
        });

        $this->assertSame([], $capturedOptions);
        $this->assertCount(1, $warningMessages);
        $this->assertStringContainsString('SENTRY_DSN is not set', $warningMessages[0]);
    }

    public function testBootstrapTreatsEmptyStringAsMissing(): void
    {
        $capturedOptions = [];
        $warningMessages = [];

        SentryBootstrapper::bootstrap('', static function (array $options) use (&$capturedOptions): void {
            $capturedOptions[] = $options;
        }, static function (string $message) use (&$warningMessages): void {
            $warningMessages[] = $message;
        });

        $this->assertSame([], $capturedOptions);
        $this->assertCount(1, $warningMessages);
        $this->assertStringContainsString('SENTRY_DSN is not set', $warningMessages[0]);
    }
}
