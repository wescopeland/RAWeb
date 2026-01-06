<?php

declare(strict_types=1);

use App\Community\Enums\ClaimStatus;
use App\Community\Enums\ClaimType;
use App\Models\Achievement;
use App\Models\AchievementGroup;
use App\Models\AchievementSetClaim;
use App\Models\Forum;
use App\Models\ForumCategory;
use App\Models\ForumTopic;
use App\Models\ForumTopicComment;
use App\Models\Game;
use App\Models\GameAchievementSet;
use App\Models\GameHash;
use App\Models\GameSet;
use App\Models\Leaderboard;
use App\Models\News;
use App\Models\Role;
use App\Models\StaticData;
use App\Models\System;
use App\Models\User;
use App\Platform\Actions\AssociateAchievementSetToGameAction;
use App\Platform\Actions\UpsertGameCoreAchievementSetFromLegacyFlagsAction;
use App\Platform\Enums\AchievementSetType;
use App\Platform\Enums\GameSetType;
use Database\Seeders\RolesTableSeeder;
use Database\Seeders\SystemsTableSeeder;
use Tests\Concerns\SeedsOnce;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(SeedsOnce::class);

useSeed(function () {
    $this->seed(RolesTableSeeder::class);
    $this->seed(SystemsTableSeeder::class);

    $user = User::factory()->create();

    $system = System::first();
    $game = Game::factory()->create(['system_id' => $system->id]);
    Achievement::factory()->promoted()->count(3)->create([
        'game_id' => $game->id,
        'user_id' => $user->id,
    ]);
    (new UpsertGameCoreAchievementSetFromLegacyFlagsAction())->execute($game);

    StaticData::factory()->create();

    News::factory()->count(3)->create(['user_id' => $user->id]);

    AchievementSetClaim::factory()->create([
        'user_id' => $user->id,
        'game_id' => $game->id,
    ]);

    $forumCategory = ForumCategory::factory()->create();
    $forum = Forum::factory()->create(['forum_category_id' => $forumCategory->id]);
    $forumTopic = ForumTopic::factory()->create([
        'forum_id' => $forum->id,
        'author_id' => $user->id,
    ]);
    ForumTopicComment::factory()->create([
        'forum_topic_id' => $forumTopic->id,
        'author_id' => $user->id,
    ]);
});

describe('Home', function () {
    it('renders for guests', function () {
        get('/')->assertSuccessful();
    });
});

describe('Game Pages', function () {
    useSeed(function () {
        $system = System::first();
        $developer = User::factory()->create(['username' => 'GameDev']);
        $developer->assignRole(Role::DEVELOPER);

        $game = Game::factory()->create([
            'title' => 'Sonic the Hedgehog',
            'system_id' => $system->id,
        ]);

        Achievement::factory()->promoted()->count(6)->create([
            'game_id' => $game->id,
            'user_id' => $developer->id,
        ]);
        Achievement::factory()->count(2)->create([
            'game_id' => $game->id,
            'user_id' => $developer->id,
            'is_promoted' => false,
        ]);
        (new UpsertGameCoreAchievementSetFromLegacyFlagsAction())->execute($game);

        $gameAchievementSet = GameAchievementSet::where('game_id', $game->id)->first();
        AchievementGroup::factory()->create([
            'achievement_set_id' => $gameAchievementSet->achievement_set_id,
            'label' => 'Test Group',
        ]);

        $subsetGame = Game::factory()->create([
            'title' => 'Sonic the Hedgehog [Subset - Bonus]',
            'system_id' => $system->id,
        ]);
        Achievement::factory()->promoted()->count(4)->create([
            'game_id' => $subsetGame->id,
            'user_id' => $developer->id,
        ]);
        (new UpsertGameCoreAchievementSetFromLegacyFlagsAction())->execute($subsetGame);
        (new AssociateAchievementSetToGameAction())->execute(
            $game,
            $subsetGame,
            AchievementSetType::Bonus,
            'Bonus'
        );

        Leaderboard::factory()->count(3)->create([
            'game_id' => $game->id,
            'order_column' => 1,
        ]);

        GameHash::factory()->count(2)->create([
            'game_id' => $game->id,
            'system_id' => $system->id,
        ]);

        AchievementSetClaim::factory()->create([
            'user_id' => $developer->id,
            'game_id' => $game->id,
            'claim_type' => ClaimType::Primary,
            'status' => ClaimStatus::Active,
        ]);

        $hub = GameSet::factory()->create([
            'type' => GameSetType::Hub,
            'title' => '[Series - Sonic]',
        ]);
        $hub->games()->attach([$game->id]);

        $similarGame = Game::factory()->create([
            'title' => 'Sonic the Hedgehog 2',
            'system_id' => $system->id,
        ]);
        $similarGamesSet = GameSet::factory()->create([
            'type' => GameSetType::SimilarGames,
            'game_id' => $game->id,
        ]);
        $similarGamesSet->games()->attach([$similarGame->id]);
    });

    it('renders for guests', function () {
        $game = Game::firstWhere('title', 'Sonic the Hedgehog');

        get(route('game.show', ['game' => $game]))->assertSuccessful();
    });

    it('renders for authenticated users', function () {
        $game = Game::firstWhere('title', 'Sonic the Hedgehog');
        $user = User::factory()->create();

        actingAs($user)->get(route('game.show', ['game' => $game]))->assertSuccessful();
    });

    it('renders for junior developers', function () {
        $game = Game::firstWhere('title', 'Sonic the Hedgehog');
        $jrDev = User::factory()->create();
        $jrDev->assignRole(Role::DEVELOPER_JUNIOR);

        actingAs($jrDev)->get(route('game.show', ['game' => $game]))->assertSuccessful();
    });

    it('renders for developers', function () {
        $game = Game::firstWhere('title', 'Sonic the Hedgehog');
        $dev = User::factory()->create();
        $dev->assignRole(Role::DEVELOPER);

        actingAs($dev)->get(route('game.show', ['game' => $game]))->assertSuccessful();
    });

    it('renders for moderators', function () {
        $game = Game::firstWhere('title', 'Sonic the Hedgehog');
        $mod = User::factory()->create();
        $mod->assignRole(Role::MODERATOR);

        actingAs($mod)->get(route('game.show', ['game' => $game]))->assertSuccessful();
    });

    it('renders with unpublished achievements view', function () {
        $game = Game::firstWhere('title', 'Sonic the Hedgehog');

        get(route('game.show', ['game' => $game, 'unpublished' => 'true']))->assertSuccessful();
    });

    it('renders with subset selected', function () {
        $game = Game::firstWhere('title', 'Sonic the Hedgehog');
        $subsetSet = GameAchievementSet::where('game_id', $game->id)
            ->where('type', AchievementSetType::Bonus)
            ->first();

        get(route('game.show', ['game' => $game, 'set' => $subsetSet->achievement_set_id]))->assertSuccessful();
    });

    it('renders leaderboards view', function () {
        $game = Game::firstWhere('title', 'Sonic the Hedgehog');

        get(route('game.show', ['game' => $game, 'view' => 'leaderboards']))->assertSuccessful();
    });
});
