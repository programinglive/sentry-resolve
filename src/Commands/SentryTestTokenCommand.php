<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Commands;

use Mahardhika\SentryResolve\SentryClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sentry:test-token',
    description: 'Test a specific Sentry token'
)]
class SentryTestTokenCommand extends Command
{
    public function __construct(private ?SentryClient $client = null)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('token', InputArgument::OPTIONAL, 'Sentry API token to test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $token = $input->getArgument('token');

        if (!$token) {
            if (!$this->client) {
                $output->writeln('<error>❌ No token provided. Pass one explicitly: sentry:test-token {token}</error>');
                return Command::INVALID;
            }

            $token = $this->client->getToken();

            if (!$token) {
                $output->writeln('<error>❌ No token configured. Set SENTRY_TOKEN or provide one as argument.</error>');
                return Command::INVALID;
            }
        }

        $output->writeln('<info>Testing Sentry token...</info>');
        $output->writeln('Token: ' . substr($token, 0, 20) . '...');

        try {
            $client = $this->client ?? new SentryClient($token, 'test', 'test');
            $auth = $client->testToken();

            $output->writeln('<info>✅ Token is valid</info>');
            $output->writeln('  User: ' . ($auth['user']['email'] ?? 'Unknown'));
            $output->writeln('  Scopes: ' . implode(', ', $auth['scopes'] ?? []));

            $requiredScopes = ['project:read', 'event:read'];
            $hasRequired = !array_diff($requiredScopes, $auth['scopes'] ?? []);

            if (!$hasRequired) {
                $output->writeln('<error>❌ Missing required scopes</error>');
                return Command::FAILURE;
            } else {
                $output->writeln('<info>✅ Token has required scopes</info>');
                return Command::SUCCESS;
            }
        } catch (\Exception $e) {
            $output->writeln('<error>❌ Invalid token: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
