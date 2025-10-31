<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Bootstrap;

final class SentryBootstrapper
{
    private const WARNING_MESSAGE = "[sentry-resolve] Warning: SENTRY_DSN is not set. Errors will not be reported to Sentry.\n";

    /**
     * @param callable|null $initializer
     * @param callable|null $warningWriter
     */
    public static function bootstrap(?string $dsn, ?callable $initializer = null, ?callable $warningWriter = null): void
    {
        $initializer ??= static function (array $options) {
            \Sentry\init($options);
        };

        $warningWriter ??= static function (string $message) {
            fwrite(STDERR, $message);
        };

        if ($dsn === null || $dsn === '') {
            $warningWriter(self::WARNING_MESSAGE);
        } else {
            $initializer(['dsn' => $dsn]);
        }
    }
}
