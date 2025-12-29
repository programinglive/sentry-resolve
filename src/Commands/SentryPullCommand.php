<?php

declare(strict_types=1);

namespace Mahardhika\SentryResolve\Commands;

use Mahardhika\SentryResolve\SentryClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'sentry:pull',
    description: 'Fetch Sentry issues and write SENTRY_TODO.md'
)]
class SentryPullCommand extends Command
{
    private ?SentryClient $client;

    public function __construct(?SentryClient $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Number of issues', 25)
            ->addOption('query', null, InputOption::VALUE_OPTIONAL, 'Sentry search query', 'is:unresolved')
            ->addOption('sort', 's', InputOption::VALUE_OPTIONAL, 'Sort order (freq|new|priority|trend|user)', 'freq')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file path', 'SENTRY_TODO.md');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->client === null) {
            $output->writeln('<error>Sentry Resolve is not configured. Please set SENTRY_TOKEN, SENTRY_ORG, and SENTRY_PROJECT in your .env file or config/sentry-resolve.php</error>');
            return Command::FAILURE;
        }

        $limit = (int) $input->getOption('limit');
        $query = $input->getOption('query');
        $sort = $input->getOption('sort');
        $outputFile = $input->getOption('output');

        $output->writeln('<info>Fetching issues from Sentry...</info>');

        try {
            $issues = $this->client->getIssues([
                'query' => $query,
                'limit' => $limit,
                'sort' => $sort,
            ]);

            if (empty($issues)) {
                $output->writeln('<comment>No issues found matching the criteria.</comment>');
                if (is_string($outputFile) && $outputFile !== '' && file_exists($outputFile)) {
                    @unlink($outputFile);
                    $output->writeln(sprintf('<info>Removed %s</info>', $outputFile));
                }
                return Command::SUCCESS;
            }

            $blocks = [];
            foreach ($issues as $issue) {
                $issueId = $issue['id'];
                $shortId = $issue['shortId'];
                $title = $issue['title'];
                $culprit = $issue['culprit'] ?? '';
                $level = $issue['level'] ?? 'error';
                $events = $issue['count'] ?? '0';
                $users = $issue['userCount'] ?? '0';
                $first = $issue['firstSeen'] ?? '';
                $last = $issue['lastSeen'] ?? '';
                $link = $issue['permalink'] ?? '';

                $blocks[] = <<<MD
### {$shortId} — {$title}
- Level: **{$level}** | Events: **{$events}** | Users: **{$users}**
- Culprit: `{$culprit}`
- First/Last seen: {$first} → {$last}
- Suggested branch: `fix/{$shortId}`
- Link: {$link}


MD;
            }

            $content = "# Sentry Fix Queue\n\n> Query: `{$query}`, Sort: `{$sort}`\n\n" . implode("\n", $blocks);
            
            file_put_contents($outputFile, $content);
            
            $output->writeln("<info>Wrote {$outputFile}</info>");
            $output->writeln("<info>Found " . count($issues) . " issues to fix</info>");

            
            // Check if dev-workflow MCP server is installed
            $hasDevWorkflow = $this->checkDevWorkflowMcp();
            
            if ($hasDevWorkflow) {
                $output->writeln('');
                $output->writeln('<fg=cyan>╔════════════════════════════════════════════════════════════════╗</>');
                $output->writeln('<fg=cyan>║</> <fg=yellow;options=bold>🤖 AI Development Workflow Detected</>                          <fg=cyan>║</>');
                $output->writeln('<fg=cyan>╚════════════════════════════════════════════════════════════════╝</>');
                $output->writeln('');
                $output->writeln('<comment>When using AI to resolve these issues, follow the MCP workflow:</comment>');
                $output->writeln('');
                $output->writeln('  <info>1.</info> <fg=green>mcp_dev-workflow_start_task</> - Start a new task');
                $output->writeln('  <info>2.</info> <fg=green>mcp_dev-workflow_mark_bug_fixed</> - Mark the issue as fixed');
                $output->writeln('  <info>3.</info> <fg=green>mcp_dev-workflow_create_tests</> - Create tests');
                $output->writeln('  <info>4.</info> <fg=green>mcp_dev-workflow_run_tests</> - Run and verify tests');
                $output->writeln('  <info>5.</info> <fg=green>mcp_dev-workflow_create_documentation</> - Document changes');
                $output->writeln('  <info>6.</info> <fg=green>mcp_dev-workflow_commit_and_push</> - Commit and push');
                $output->writeln('  <info>7.</info> <fg=green>mcp_dev-workflow_complete_task</> - Complete the task');
                $output->writeln('');
                $output->writeln('<comment>📦 Package: @programinglive/dev-workflow-mcp-server</comment>');
                $output->writeln('');
            }
            
            $output->writeln('');
            $output->writeln('<comment>Tip: To resolve these issues, use the following command:</comment>');
            $output->writeln('<info>php artisan sentry:resolve {ID}</info>');
            if (isset($issues[0]['shortId'])) {
                $output->writeln('<comment>Example: php artisan sentry:resolve ' . $issues[0]['shortId'] . '</comment>');
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Error: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }
    
    /**
     * Check if @programinglive/dev-workflow-mcp-server is installed
     */
    private function checkDevWorkflowMcp(): bool
    {
        // Check for node_modules directory
        $nodeModulesPath = getcwd() . '/node_modules/@programinglive/dev-workflow-mcp-server';
        if (is_dir($nodeModulesPath)) {
            return true;
        }
        
        // Check package.json for the dependency
        $packageJsonPath = getcwd() . '/package.json';
        if (file_exists($packageJsonPath)) {
            $packageJson = json_decode(file_get_contents($packageJsonPath), true);
            if (isset($packageJson['dependencies']['@programinglive/dev-workflow-mcp-server']) ||
                isset($packageJson['devDependencies']['@programinglive/dev-workflow-mcp-server'])) {
                return true;
            }
        }
        
        return false;
    }
}
