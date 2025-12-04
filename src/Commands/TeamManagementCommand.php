<?php

namespace PicoBaz\Sentinel\Commands;

use Illuminate\Console\Command;
use PicoBaz\Sentinel\Modules\TeamCollaboration\TeamHelper;
use PicoBaz\Sentinel\Models\Team;
use PicoBaz\Sentinel\Models\TeamMember;

class TeamManagementCommand extends Command
{
    protected $signature = 'sentinel:team 
                            {action : create, list, members, stats, leaderboard}
                            {--team= : Team ID or slug}
                            {--user= : User ID}
                            {--period=all : Period for stats (all, week, month)}';
    
    protected $description = 'Manage Sentinel teams and view statistics';

    public function handle()
    {
        $action = $this->argument('action');

        return match($action) {
            'create' => $this->createTeam(),
            'list' => $this->listTeams(),
            'members' => $this->listMembers(),
            'stats' => $this->showStats(),
            'leaderboard' => $this->showLeaderboard(),
            default => $this->error("Unknown action: {$action}"),
        };
    }

    protected function createTeam()
    {
        $name = $this->ask('Team name:');
        $description = $this->ask('Team description (optional):');
        $slug = \Illuminate\Support\Str::slug($name);

        $team = Team::create([
            'name' => $name,
            'description' => $description,
            'slug' => $slug,
        ]);

        $this->info("✅ Team '{$name}' created successfully!");
        $this->line("Team ID: {$team->id}");
        $this->line("Slug: {$slug}");

        return 0;
    }

    protected function listTeams()
    {
        $teams = Team::with('members')->get();

        if ($teams->isEmpty()) {
            $this->warn('No teams found.');
            return 0;
        }

        $this->info('📋 Sentinel Teams');
        $this->line('');

        $data = $teams->map(function ($team) {
            return [
                $team->id,
                $team->name,
                $team->slug,
                $team->members->count(),
            ];
        });

        $this->table(['ID', 'Name', 'Slug', 'Members'], $data);

        return 0;
    }

    protected function listMembers()
    {
        $teamOption = $this->option('team');

        if (!$teamOption) {
            $this->error('Please specify --team option');
            return 1;
        }

        $team = is_numeric($teamOption) 
            ? Team::find($teamOption)
            : Team::where('slug', $teamOption)->first();

        if (!$team) {
            $this->error('Team not found');
            return 1;
        }

        $members = TeamHelper::getTeamMembers($team->id);

        if ($members->isEmpty()) {
            $this->warn("No members in team '{$team->name}'");
            return 0;
        }

        $this->info("👥 Team '{$team->name}' Members");
        $this->line('');

        $data = $members->map(function ($user) use ($team) {
            $member = TeamMember::where('team_id', $team->id)
                ->where('user_id', $user->id)
                ->first();

            return [
                $user->id,
                $user->name ?? 'Unknown',
                $user->email ?? 'N/A',
                $member->role ?? 'member',
                $member->points ?? 0,
                count($member->badges ?? []),
            ];
        });

        $this->table(['ID', 'Name', 'Email', 'Role', 'Points', 'Badges'], $data);

        return 0;
    }

    protected function showStats()
    {
        $userId = $this->option('user');

        if (!$userId) {
            $this->error('Please specify --user option');
            return 1;
        }

        $stats = TeamHelper::getUserStats($userId);

        $this->info("📊 User Statistics (ID: {$userId})");
        $this->line('');

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Resolved', $stats['total_resolved']],
                ['Total Assigned', $stats['total_assigned']],
                ['Open Issues', $stats['open_issues']],
                ['In Progress', $stats['in_progress']],
                ['Avg Resolution Time', $stats['average_resolution_time'] . ' min'],
                ['Points', $stats['points']],
                ['Badges', count($stats['badges'])],
            ]
        );

        if (!empty($stats['badges'])) {
            $this->line('');
            $this->info('🏆 Badges:');
            foreach ($stats['badges'] as $badge) {
                $this->line("  • " . $this->formatBadgeName($badge));
            }
        }

        return 0;
    }

    protected function showLeaderboard()
    {
        $teamOption = $this->option('team');
        $period = $this->option('period');

        if ($teamOption) {
            $team = is_numeric($teamOption) 
                ? Team::find($teamOption)
                : Team::where('slug', $teamOption)->first();

            if (!$team) {
                $this->error('Team not found');
                return 1;
            }

            $leaderboard = TeamHelper::getTeamLeaderboard($team->id, $period);
            $title = "Team '{$team->name}' Leaderboard";
        } else {
            $leaderboard = TeamHelper::getGlobalLeaderboard();
            $title = "Global Leaderboard";
            $period = 'all';
        }

        $this->info("🏆 {$title} ({$period})");
        $this->line('');

        if ($leaderboard->isEmpty()) {
            $this->warn('No data available');
            return 0;
        }

        $data = $leaderboard->map(function ($entry, $index) {
            $rank = $index + 1;
            $medal = match($rank) {
                1 => '🥇',
                2 => '🥈',
                3 => '🥉',
                default => "#{$rank}",
            };

            return [
                $medal,
                $entry['name'],
                $entry['points'],
                $entry['resolved_issues'] ?? 'N/A',
                count($entry['badges'] ?? []),
            ];
        });

        $this->table(['Rank', 'Name', 'Points', 'Resolved', 'Badges'], $data);

        return 0;
    }

    protected function formatBadgeName($badge)
    {
        return ucwords(str_replace('_', ' ', $badge));
    }
}
