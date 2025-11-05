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
    private SentryClient $client;

    public function __construct(SentryClient $client)
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
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln("<error>Error: " . $e->getMessage() . "</error>");
            return Command::FAILURE;
        }
    }
}
