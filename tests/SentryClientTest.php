<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Tests;

use Mahardhika\SentryResolve\SentryClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;

class SentryClientTest extends TestCase
{
    private function createMockClient(array $responses): SentryClient
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

    public function testConstructorWithValidParameters(): void
    {
        $client = new SentryClient('token', 'org', 'project');
        $this->assertInstanceOf(SentryClient::class, $client);
    }

    public function testConstructorWithEmptyToken(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SentryClient('', 'org', 'project');
    }

    public function testConstructorWithEmptyOrganization(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SentryClient('token', '', 'project');
    }

    public function testConstructorWithEmptyProject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SentryClient('token', 'org', '');
    }

    public function testTestTokenSuccess(): void
    {
        $response = new Response(200, [], json_encode([
            'user' => ['email' => 'test@example.com'],
            'scopes' => ['project:read', 'event:read']
        ]));

        $client = $this->createMockClient([$response]);
        $result = $client->testToken();

        $this->assertEquals('test@example.com', $result['user']['email']);
        $this->assertContains('project:read', $result['scopes']);
    }

    public function testGetIssuesSuccess(): void
    {
        $issuesData = [
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

        $response = new Response(200, [], json_encode($issuesData));
        $client = $this->createMockClient([$response]);
        $result = $client->getIssues();

        $this->assertCount(1, $result);
        $this->assertEquals('TEST-1', $result[0]['shortId']);
    }

    public function testResolveIssueSuccess(): void
    {
        $response = new Response(200, [], json_encode(['status' => 'resolved']));
        $client = $this->createMockClient([$response]);
        $result = $client->resolveIssue('TEST-1');

        $this->assertTrue($result);
    }

    public function testResolveIssuesMultiple(): void
    {
        $responses = [
            new Response(200, [], json_encode(['status' => 'resolved'])),
            new Response(200, [], json_encode(['status' => 'resolved'])),
        ];
        
        $client = $this->createMockClient($responses);
        $results = $client->resolveIssues(['TEST-1', 'TEST-2']);

        $this->assertTrue($results['TEST-1']);
        $this->assertTrue($results['TEST-2']);
    }

    public function testGetters(): void
    {
        $client = new SentryClient('test-token', 'test-org', 'test-proj');
        
        $this->assertEquals('test-token', $client->getToken());
        $this->assertEquals('test-org', $client->getOrganization());
        $this->assertEquals('test-proj', $client->getProject());
    }
}
