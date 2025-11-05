<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Tests\Commands;

use Mahardhika\SentryResolve\Commands\SentryPullCommand;
use Mahardhika\SentryResolve\SentryClient;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class SentryPullCommandTest extends TestCase
{
    /** @var SentryClient&MockObject */
    private $client;
    private CommandTester $commandTester;

    protected function tearDown(): void
    {
        foreach (['test-output.md', 'SENTRY_TODO.md', 'existing-output.md'] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    protected function setUp(): void
    {
        $this->client = $this->createMock(SentryClient::class);
        $command = new SentryPullCommand($this->client);
        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteSuccess(): void
    {
        $issues = [
            [
                'id' => '1',
                'shortId' => 'TEST-1',
                'title' => 'Test Issue',
                'level' => 'error',
                'count' => 10,
                'userCount' => 5,
                'culprit' => 'TestController.php',
                'firstSeen' => '2023-01-01T00:00:00Z',
                'lastSeen' => '2023-01-02T00:00:00Z',
                'permalink' => 'https://sentry.io/issues/1'
            ]
        ];

        $this->client
            ->expects($this->once())
            ->method('getIssues')
            ->with([
                'query' => 'is:unresolved',
                'limit' => 25,
                'sort' => 'freq',
            ])
            ->willReturn($issues);

        $exitCode = $this->commandTester->execute([
            '--limit' => 25,
            '--query' => 'is:unresolved',
            '--sort' => 'freq',
            '--output' => 'test-output.md'
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertStringContainsString('Found 1 issues to fix', $this->commandTester->getDisplay());
        $this->assertFileExists('test-output.md');
        
        // Clean up
        if (file_exists('test-output.md')) {
            unlink('test-output.md');
        }
    }

    public function testExecuteWithNoIssues(): void
    {
        $this->client
            ->expects($this->once())
            ->method('getIssues')
            ->willReturn([]);

        $defaultOutput = 'SENTRY_TODO.md';
        file_put_contents($defaultOutput, 'existing content');

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(0, $exitCode);
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('No issues found', $display);
        $this->assertStringContainsString('Removed ' . $defaultOutput, $display);
        $this->assertFileDoesNotExist($defaultOutput);
    }

    public function testExecuteWithNoIssuesRemovesExistingFile(): void
    {
        $this->client
            ->expects($this->once())
            ->method('getIssues')
            ->willReturn([]);

        $outputFile = 'existing-output.md';
        file_put_contents($outputFile, 'existing');

        $exitCode = $this->commandTester->execute([
            '--output' => $outputFile,
        ]);

        $this->assertEquals(0, $exitCode);
        $display = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Removed ' . $outputFile, $display);
        $this->assertFileDoesNotExist($outputFile);
    }

    public function testExecuteWithException(): void
    {
        $this->client
            ->expects($this->once())
            ->method('getIssues')
            ->willThrowException(new \RuntimeException('API Error'));

        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(1, $exitCode);
        $this->assertStringContainsString('Error: API Error', $this->commandTester->getDisplay());
    }
}
