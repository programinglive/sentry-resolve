<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Tests\Commands;

use Mahardhika\SentryResolve\Commands\SentryResolveCommand;
use Mahardhika\SentryResolve\SentryClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class SentryResolveCommandTest extends TestCase
{
    private $client;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->client = $this->createMock(SentryClient::class);
        $command = new SentryResolveCommand($this->client);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteSuccess(): void
    {
        $this->client
            ->expects($this->once())
            ->method('resolveIssue')
            ->with('TEST-1')
            ->willReturn(true);

        $exitCode = $this->commandTester->execute([
            'identifiers' => ['TEST-1']
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('✓ Resolved issue TEST-1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('✓ Success: 1', $this->commandTester->getDisplay());
    }

    public function testExecuteWithMultipleIssues(): void
    {
        $calls = [];

        $this->client
            ->expects($this->exactly(2))
            ->method('resolveIssue')
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
    }

    public function testExecuteWithFailure(): void
    {
        $this->client
            ->expects($this->once())
            ->method('resolveIssue')
            ->with('TEST-1')
            ->willThrowException(new \RuntimeException('API Error'));

        $exitCode = $this->commandTester->execute([
            'identifiers' => ['TEST-1']
        ]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('✗ Failed to resolve issue TEST-1', $this->commandTester->getDisplay());
        $this->assertStringContainsString('✗ Failed: 1', $this->commandTester->getDisplay());
    }

    public function testExecuteWithMixedResults(): void
    {
        $callIndex = 0;
        $expectedIdentifiers = ['TEST-1', 'TEST-2'];

        $this->client
            ->expects($this->exactly(2))
            ->method('resolveIssue')
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
    }
}
