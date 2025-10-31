<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mahardhika\SentryResolve\SentryClient;
use PHPUnit\Framework\TestCase;

class SentryClientResolveTest extends TestCase
{
    private function createClient(array $responses): SentryClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $client = new Client([
            'handler' => $handlerStack,
            'base_uri' => 'https://sentry.io/api/0/',
            'headers' => ['Authorization' => 'Bearer test-token'],
        ]);

        return new SentryClient('test-token', 'test-org', 'test-proj', $client);
    }

    public function testResolveIssueByIdentifierWithNumericId(): void
    {
        $responses = [
            new Response(200, [], json_encode(['status' => 'resolved'])),
        ];

        $client = $this->createClient($responses);

        $this->assertTrue($client->resolveIssueByIdentifier('123456789'));
    }

    public function testResolveIssueByIdentifierWithShortId(): void
    {
        $responses = [
            new Response(200, [], json_encode([
                [
                    'id' => '123456789',
                    'shortId' => 'SENTRY-123',
                ],
            ])),
            new Response(200, [], json_encode(['status' => 'resolved'])),
        ];

        $client = $this->createClient($responses);

        $this->assertTrue($client->resolveIssueByIdentifier('SENTRY-123'));
    }

    public function testResolveIssueByIdentifierThrowsWhenShortIdNotFound(): void
    {
        $responses = [
            new Response(200, [], json_encode([])),
        ];

        $client = $this->createClient($responses);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to resolve issue SENTRY-404: Issue not found');

        $client->resolveIssueByIdentifier('SENTRY-404');
    }
}
