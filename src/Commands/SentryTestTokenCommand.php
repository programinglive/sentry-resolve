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
    protected function configure(): void
    {
        $this
            ->addArgument('token', InputArgument::REQUIRED, 'Sentry API token to test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $token = $input->getArgument('token');

        $output->writeln('<info>Testing Sentry token...</info>');
        $output->writeln('Token: ' . substr($token, 0, 20) . '...');

        try {
            $client = new SentryClient($token, 'test', 'test');
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
