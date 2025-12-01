<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Tests\Commands;

use Mahardhika\SentryResolve\Commands\SentryResolveCommand;
use Mahardhika\SentryResolve\Logging\ResolutionLogger;
use Mahardhika\SentryResolve\SentryClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SentryResolveCommandTest extends TestCase
{
    /** @var SentryClient&MockObject */
    private $client;
    private string $logDir;
    private string $logPrefix;
    private ResolutionLogger $logger;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->client = $this->createMock(SentryClient::class);
        $this->logDir = sys_get_temp_dir() . '/sentry-resolve-test-' . uniqid();
        mkdir($this->logDir, 0777, true);
        $this->logPrefix = 'test-log-' . uniqid();
        $this->logger = new ResolutionLogger($this->logDir, 'daily', $this->logPrefix);

        $command = new SentryResolveCommand($this->client, $this->logger);
        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->logDir)) {
            $this->recursiveDelete($this->logDir);
        }
    }

    private function recursiveDelete(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->recursiveDelete($path);
            } else {
                unlink($path);
            }
        }

        rmdir($directory);
    }

    private function getLogContent(): string
    {
        $files = glob($this->logDir . '/' . $this->logPrefix . '-*.log');
        if (!$files) {
            return '';
        }

        return file_get_contents($files[0]) ?: '';
    }

    public function testExecuteSuccess(): void
    {
        $this->client
            ->expects($this->once())
            ->method('resolveIssueByIdentifier')
            ->with('TEST-1')
            ->willReturn(true);

        $exitCode = $this->commandTester->execute([
            'identifiers' => ['TEST-1']
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('✓ Resolved issue TEST-1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('✓ Success: 1', $this->commandTester->getDisplay());

        $logContent = $this->getLogContent();
        $this->assertStringContainsString('SUCCESS: TEST-1', $logContent);
    }

    public function testExecuteWithMultipleIssues(): void
    {
        $calls = [];

        $this->client
            ->expects($this->exactly(2))
            ->method('resolveIssueByIdentifier')
            ->willReturnCallback(function (string $identifier) use (&$calls) {
                $calls[] = $identifier;
                return true;
            });

        $exitCode = $this->commandTester->execute([
            'identifiers' => ['TEST-1', 'TEST-2']
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('✓ Success: 2', $this->commandTester->getDisplay());
        $this->assertSame(['TEST-1', 'TEST-2'], $calls);

        $logContent = $this->getLogContent();
        $this->assertStringContainsString('SUCCESS: TEST-1', $logContent);
        $this->assertStringContainsString('SUCCESS: TEST-2', $logContent);
    }

    public function testExecuteWithFailure(): void
    {
        $this->client
            ->expects($this->once())
            ->method('resolveIssueByIdentifier')
            ->with('TEST-1')
            ->willThrowException(new \RuntimeException('API Error'));

        $exitCode = $this->commandTester->execute([
            'identifiers' => ['TEST-1']
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('✗ Failed to resolve issue TEST-1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('✗ Failed: 1', $this->commandTester->getDisplay());

        $logContent = $this->getLogContent();
        $this->assertStringContainsString('FAILURE: TEST-1', $logContent);
        $this->assertStringContainsString('API Error', $logContent);
    }

    public function testExecuteWithMixedResults(): void
    {
        $callIndex = 0;
        $expectedIdentifiers = ['TEST-1', 'TEST-2'];

        $this->client
            ->expects($this->exactly(2))
            ->method('resolveIssueByIdentifier')
            ->willReturnCallback(function (string $identifier) use (&$callIndex, $expectedIdentifiers) {
                $this->assertSame($expectedIdentifiers[$callIndex], $identifier);

                if ($callIndex === 0) {
                    $callIndex++;
                    return true;
                }

                $callIndex++;
                throw new \RuntimeException('API Error');
            });

        $exitCode = $this->commandTester->execute([
            'identifiers' => ['TEST-1', 'TEST-2']
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('✓ Success: 1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('✗ Failed: 1', $this->commandTester->getDisplay());
        $this->assertSame(2, $callIndex);

        $logContent = $this->getLogContent();
        $this->assertStringContainsString('SUCCESS: TEST-1', $logContent);
        $this->assertStringContainsString('FAILURE: TEST-2', $logContent);
        $this->assertStringContainsString('API Error', $logContent);
    }

    public function testExecuteWithoutConfiguredClient(): void
    {
        $command = new SentryResolveCommand(null);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([
            'identifiers' => ['TEST-1']
        ]);

        $this->assertEquals(1, $exitCode);
        $display = $commandTester->getDisplay();
        $this->assertStringContainsString('Sentry Resolve is not configured', $display);
        $this->assertStringContainsString('SENTRY_TOKEN', $display);
        $this->assertStringContainsString('SENTRY_ORG', $display);
        $this->assertStringContainsString('SENTRY_PROJECT', $display);
    }
}
