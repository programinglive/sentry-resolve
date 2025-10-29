<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Commands;

use Mahardhika\SentryResolve\Logging\ResolutionLogger;
use Mahardhika\SentryResolve\SentryClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sentry:resolve',
    description: 'Resolve Sentry issues via the Sentry API'
)]
class SentryResolveCommand extends Command
{
    private SentryClient $client;
    private ?ResolutionLogger $logger;

    public function __construct(SentryClient $client, ?ResolutionLogger $logger = null)
    {
        parent::__construct();
        $this->client = $client;
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('identifiers', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'One or more Sentry issue identifiers (e.g., POS-CLINIC-P)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $identifiers = $input->getArgument('identifiers');
        $successCount = 0;
        $failureCount = 0;

        foreach ($identifiers as $identifier) {
            try {
                $output->writeln("<info>Resolving issue {$identifier}...</info>");
                $this->client->resolveIssue($identifier);
                $output->writeln("<info>✓ Resolved issue {$identifier}</info>");
                $this->logger?->logSuccess($identifier);
                $successCount++;
            } catch (\Exception $e) {
                $output->writeln("<error>✗ Failed to resolve issue {$identifier}: " . $e->getMessage() . "</error>");
                $this->logger?->logFailure($identifier, $e->getMessage());
                $failureCount++;
            }
        }

        $output->writeln('');
        $output->writeln("<info>Resolution complete:</info>");
        $output->writeln("  <info>✓ Success: {$successCount}</info>");
        if ($failureCount > 0) {
            $output->writeln("  <error>✗ Failed: {$failureCount}</error>");
        }

        return $failureCount === 0 ? Command::SUCCESS : Command::FAILURE;
    }
}
