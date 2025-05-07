<?php

declare(strict_types=1);

namespace App\Platform\Commands;

use App\Models\PlayerAchievementSet;
use App\Models\PlayerGame;
use App\Models\System;
use App\Platform\Enums\AchievementSetType;
use App\Platform\Jobs\ProcessPlayerEstimatedTimeJob;
use App\Platform\Services\PlayerGameActivityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class UpdatePlayerEstimatedTimes extends Command
{
    protected $signature = 'ra:platform:player:update-estimated-times';

    protected $description = 'Updates estimated play times for player_games';

    public function handle(): void
    {
        $playerGames = PlayerGame::whereNull('playtime_total');
        $count = $playerGames->count();

        $this->info("Preparing batch jobs to update estimated times for {$count} player games.");

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $playerGames->chunkById(1000, function ($chunk) use ($progressBar) {
            $jobs = $chunk->map(function ($playerGame) {
                return new ProcessPlayerEstimatedTimeJob($playerGame->id);
            })->all();

            Bus::batch($jobs)
                ->name('Update player estimated times')
                ->onQueue('player-estimated-times')
                ->allowFailures()
                ->dispatch();

            $progressBar->advance(count($chunk));
        });

        $progressBar->finish();
        $this->info("\nAll jobs have been dispatched successfully.");
    }
}
