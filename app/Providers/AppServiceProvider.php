<?php

declare(strict_types=1);

namespace App\Providers;

use App\Components\GeneralNotificationsIcon;
use App\Components\TicketNotificationsIcon;
use App\Console\Commands\CacheMostPopularEmulators;
use App\Console\Commands\CacheMostPopularSystems;
use App\Console\Commands\CleanupAvatars;
use App\Console\Commands\DeleteExpiredEmailVerificationTokens;
use App\Console\Commands\DeleteOverdueUserAccounts;
use App\Console\Commands\GenerateTypeScript;
use App\Console\Commands\LogUsersOnlineCount;
use App\Console\Commands\MakeJsComponent;
use App\Console\Commands\SyncUsers;
use App\Console\Commands\SystemAlert;
use App\Http\InertiaResponseFactory;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\ResponseFactory;
use Laravel\Pulse\Facades\Pulse;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Override Inertia's ResponseFactory to use our custom factory that strips nulls.
        // This can eliminate unnecessary props and speed up hydration.
        $this->app->singleton(ResponseFactory::class, InertiaResponseFactory::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CacheMostPopularEmulators::class,
                CacheMostPopularSystems::class,
                DeleteExpiredEmailVerificationTokens::class,
                DeleteOverdueUserAccounts::class,
                GenerateTypeScript::class,
                LogUsersOnlineCount::class,

                // User Accounts
                CleanupAvatars::class,
                SyncUsers::class,

                // Settings
                SystemAlert::class,

                // Generators
                MakeJsComponent::class,
            ]);
        }

        Model::shouldBeStrict(!$this->app->isProduction());

        Pulse::user(fn (User $user) => [
            'name' => $user->User,
            'avatar' => $user->avatarUrl,
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command(LogUsersOnlineCount::class)->everyThirtyMinutes();

            if (app()->environment() === 'production') {
                $schedule->command(DeleteExpiredEmailVerificationTokens::class)->daily();
                $schedule->command(DeleteOverdueUserAccounts::class)->daily();

                $schedule->command(CacheMostPopularEmulators::class)->weeklyOn(4, '8:00'); // Thursdays, ~3:00AM US Eastern
                $schedule->command(CacheMostPopularSystems::class)->weeklyOn(4, '8:30'); // Thursdays, ~3:30AM US Eastern
            }
        });

        Blade::if('hasfeature', function ($feature) {
            return config("feature.$feature", false);
        });

        /*
         * https://josephsilber.com/posts/2018/07/02/eloquent-polymorphic-relations-morph-map
         */
        Relation::morphMap([
            'role' => Role::class,
            'user' => User::class,
        ]);

        /*
         * Register Support Livewire components
         */
        // Livewire::component('grid', Grid::class);

        /*
         * Register Livewire components
         */
        Livewire::component('general-notifications-icon', GeneralNotificationsIcon::class);
        Livewire::component('ticket-notifications-icon', TicketNotificationsIcon::class);
        // Livewire::component('supersearch', Supersearch::class);
        // Livewire::component('user-grid', UserGrid::class);

        /*
         * Apply domain namespaces to tests' class name resolvers
         */
        Factory::guessFactoryNamesUsing(fn ($modelName) => 'Database\\Factories\\' . class_basename($modelName) . 'Factory');
        Factory::guessModelNamesUsing(function ($factory) {
            $factoryBasename = Str::replaceLast('Factory', '', class_basename($factory));

            return 'App\\Models\\' . $factoryBasename;
        });

        // TODO remove
        $this->app->singleton('mysqli', function () {
            try {
                $db = mysqli_connect(
                    config('database.connections.mysql.host'),
                    config('database.connections.mysql.username'),
                    config('database.connections.mysql.password'),
                    config('database.connections.mysql.database'),
                    (int) config('database.connections.mysql.port')
                );
                if (!$db) {
                    throw new Exception('Could not connect to database. Please try again later.');
                }
                mysqli_set_charset($db, config('database.connections.mysql.charset'));
                mysqli_query($db, "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

                return $db;
            } catch (Exception $exception) {
                if (app()->environment('local', 'testing')) {
                    throw $exception;
                }
                echo 'Could not connect to database. Please try again later.';
                exit;
            }
        });
    }
}
