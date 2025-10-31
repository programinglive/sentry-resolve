<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;

class SentryClient
{
    private Client $client;
    private string $token;
    private string $organization;
    private string $project;

    public function __construct(string $token, string $organization, string $project, ?Client $client = null)
    {
        if (empty($token) || empty($organization) || empty($project)) {
            throw new InvalidArgumentException('Token, organization, and project are required');
        }

        $this->token = $token;
        $this->organization = $organization;
        $this->project = $project;

        $this->client = $client ?? new Client([
            'base_uri' => 'https://sentry.io/api/0/',
            'headers' => ['Authorization' => "Bearer {$token}"],
            'timeout' => 30,
        ]);
    }

    public function testToken(): array
    {
        try {
            $response = $this->client->get('auth/');
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to test token: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getIssues(array $options = []): array
    {
        $defaultOptions = [
            'query' => 'is:unresolved',
            'limit' => 25,
            'sort' => 'freq',
            'expand' => 'owners,stats',
            'shortIdLookup' => 1,
        ];

        $options = array_merge($defaultOptions, $options);

        try {
            $response = $this->client->get("projects/{$this->organization}/{$this->project}/issues/", [
                'query' => $options,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to fetch issues: ' . $e->getMessage(), 0, $e);
        }
    }

    public function resolveIssue(string $issueId): bool
    {
        try {
            $this->client->request('PUT', "issues/{$issueId}/", [
                'json' => ['status' => 'resolved'],
            ]);

            return true;
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Failed to resolve issue {$issueId}: " . $e->getMessage(), 0, $e);
        }
    }

    public function resolveIssueByIdentifier(string $identifier): bool
    {
        if ($this->isNumericIdentifier($identifier)) {
            return $this->resolveIssue($identifier);
        }

        $issueId = $this->findIssueIdByShortId($identifier);

        if ($issueId === null) {
            throw new \RuntimeException("Failed to resolve issue {$identifier}: Issue not found");
        }

        return $this->resolveIssue($issueId);
    }

    public function resolveIssues(array $issueIds): array
    {
        $results = [];
        foreach ($issueIds as $issueId) {
            try {
                $results[$issueId] = $this->resolveIssueByIdentifier($issueId);
            } catch (\RuntimeException $e) {
                $results[$issueId] = false;
            }
        }

        return $results;
    }

    public function getIssue(string $issueId): array
    {
        try {
            $response = $this->client->get("issues/{$issueId}/", [
                'query' => [
                    'expand' => 'owners,stats',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException("Failed to fetch issue {$issueId}: " . $e->getMessage(), 0, $e);
        }
    }

    public function getProjects(): array
    {
        try {
            $response = $this->client->get("organizations/{$this->organization}/projects/");
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to fetch projects: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getOrganizations(): array
    {
        try {
            $response = $this->client->get('organizations/');
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to fetch organizations: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getOrganization(): string
    {
        return $this->organization;
    }

    public function getProject(): string
    {
        return $this->project;
    }

    private function findIssueIdByShortId(string $shortId): ?string
    {
        $issues = $this->getIssues([
            'query' => $shortId,
            'limit' => 1,
        ]);

        if (empty($issues)) {
            return null;
        }

        $issue = $issues[0];

        $issueShortId = isset($issue['shortId']) ? strtoupper((string) $issue['shortId']) : null;
        $normalizedShortId = strtoupper($shortId);

        if ($issueShortId !== null && $issueShortId !== $normalizedShortId) {
            return null;
        }

        return isset($issue['id']) ? (string) $issue['id'] : null;
    }

    private function isNumericIdentifier(string $identifier): bool
    {
        return preg_match('/^\d+$/', $identifier) === 1;
    }
}
