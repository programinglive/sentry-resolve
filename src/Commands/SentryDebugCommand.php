<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Commands;

use Mahardhika\SentryResolve\SentryClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sentry:debug',
    description: 'Test Sentry configuration'
)]
class SentryDebugCommand extends Command
{
    private SentryClient $client;

    public function __construct(SentryClient $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    protected function configure(): void
    {
        // No additional configuration needed
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>Testing Sentry configuration...</info>');

        $token = $this->client->getToken();
        $org = $this->client->getOrganization();
        $project = $this->client->getProject();

        $output->writeln('Token: ' . ($token ? substr($token, 0, 20) . '...' : 'NOT SET'));
        $output->writeln('Org: ' . ($org ?? 'NOT SET'));
        $output->writeln('Project: ' . ($project ?? 'NOT SET'));

        if ($token && $org && $project) {
            $output->writeln('<info>✅ Configuration looks good!</info>');
            return Command::SUCCESS;
        } else {
            $output->writeln('<error>❌ Missing configuration</error>');
            return Command::FAILURE;
        }
    }
}
