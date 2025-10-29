<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Logging;

use DateTimeImmutable;

final class ResolutionLogger
{
    private string $directory;
    private string $frequency;
    private string $prefix;

    public function __construct(string $directory, string $frequency = 'daily', string $prefix = 'sentry-resolve')
    {
        $this->directory = rtrim(is_callable($directory) ? $directory() : $directory, DIRECTORY_SEPARATOR);
        $this->frequency = strtolower($frequency);
        $this->prefix = $prefix;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getFrequency(): string
    {
        return $this->frequency;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function logSuccess(string $identifier): void
    {
        $this->writeEntry($identifier, 'SUCCESS');
    }

    public function logFailure(string $identifier, string $reason): void
    {
        $this->writeEntry($identifier, 'FAILURE', $reason);
    }

    private function writeEntry(string $identifier, string $status, ?string $message = null): void
    {
        $path = $this->resolveFilePath();
        $this->ensureDirectoryExists(dirname($path));

        $timestamp = (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        $line = sprintf('[%s] %s: %s%s%s',
            $timestamp,
            $status,
            $identifier,
            $message !== null ? ' - ' : '',
            $message !== null ? str_replace(["\r", "\n"], ' ', $message) : ''
        );

        file_put_contents($path, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    private function resolveFilePath(): string
    {
        $date = new DateTimeImmutable('now');
        $suffix = match ($this->frequency) {
            'daily' => '-' . $date->format('Y-m-d'),
            'monthly' => '-' . $date->format('Y-m'),
            'yearly' => '-' . $date->format('Y'),
            default => '',
        };

        $filename = $this->prefix . $suffix . '.log';

        return $this->directory . DIRECTORY_SEPARATOR . $filename;
    }
}
