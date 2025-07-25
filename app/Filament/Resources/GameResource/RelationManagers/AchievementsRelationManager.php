<?php

declare(strict_types=1);

namespace App\Filament\Resources\GameResource\RelationManagers;

use App\Filament\Resources\AchievementAuthorshipCreditFormSchema;
use App\Filament\Resources\AchievementResource;
use App\Models\Achievement;
use App\Models\AchievementAuthor;
use App\Models\Game;
use App\Models\System;
use App\Models\User;
use App\Platform\Actions\SyncAchievementSetOrderColumnsFromDisplayOrdersAction;
use App\Platform\Enums\AchievementAuthorTask;
use App\Platform\Enums\AchievementFlag;
use App\Platform\Enums\AchievementType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AchievementsRelationManager extends RelationManager
{
    protected static string $relationship = 'achievements';

    protected static ?string $title = 'Achievements';

    protected static ?string $icon = 'fas-trophy';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        /** @var User $user */
        $user = Auth::user();

        if ($ownerRecord instanceof Game) {
            return $user->can('manage', $ownerRecord);
        }

        return false;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        /** @var Game $game */
        $game = $ownerRecord;

        $count = $game->achievements()->published()->count();

        return $count > 0 ? "{$count}" : null;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        /** @var User $user */
        $user = Auth::user();

        return $table
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('activeMaintainer.user'))
            ->columns([
                Tables\Columns\ImageColumn::make('badge_url')
                    ->label('')
                    ->size(config('media.icon.md.width')),

                Tables\Columns\TextColumn::make('title')
                    ->description(fn (Achievement $record): string => $record->description)
                    ->wrap(),

                Tables\Columns\ViewColumn::make('MemAddr')
                    ->label('Code')
                    ->view('filament.tables.columns.achievement-code')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        AchievementType::Missable => 'Missable',
                        AchievementType::Progression => 'Progression',
                        AchievementType::WinCondition => 'Win Condition',
                        default => '',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        AchievementType::Missable => 'warning',
                        AchievementType::Progression => 'info',
                        AchievementType::WinCondition => 'success',
                        default => '',
                    })
                    ->badge(),

                Tables\Columns\TextColumn::make('points')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('DateCreated')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('DateModified')
                    ->date()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('DisplayOrder')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('activeMaintainer')
                    ->label('Maintainer')
                    ->formatStateUsing(function (Achievement $record) {
                        return $record->activeMaintainer?->user?->display_name ?? $record->developer?->display_name;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filters\SelectFilter::make('Flags')
                    ->options([
                        0 => 'All',
                        AchievementFlag::OfficialCore->value => AchievementFlag::OfficialCore->label(),
                        AchievementFlag::Unofficial->value => AchievementFlag::Unofficial->label(),
                    ])
                    ->default(AchievementFlag::OfficialCore->value)
                    ->selectablePlaceholder(false)
                    ->placeholder('All')
                    ->query(function (array $data, Builder $query) {
                        if ((bool) $data['value']) {
                            $query->where('Flags', $data['value']);
                        }
                    }),

                Filters\TernaryFilter::make('duplicate_badges')
                    ->label('Has duplicate badge')
                    ->placeholder('Any')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereExists(function ($subquery) {
                            $subquery->selectRaw('1')
                                ->from('Achievements as a2')
                                ->whereColumn('a2.GameID', 'Achievements.GameID')
                                ->whereColumn('a2.BadgeName', 'Achievements.BadgeName')
                                ->where('a2.ID', '!=', DB::raw('Achievements.ID'))
                                ->whereNull('a2.deleted_at')
                                ->limit(1);
                        }),
                        false: fn (Builder $query): Builder => $query->whereNotExists(function ($subquery) {
                            $subquery->selectRaw('1')
                                ->from('Achievements as a2')
                                ->whereColumn('a2.GameID', 'Achievements.GameID')
                                ->whereColumn('a2.BadgeName', 'Achievements.BadgeName')
                                ->where('a2.ID', '!=', DB::raw('Achievements.ID'))
                                ->whereNull('a2.deleted_at')
                                ->limit(1);
                        }),
                        blank: fn (Builder $query): Builder => $query,
                    ),
            ])
            ->headerActions([

            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Action::make('assign-maintainer')
                        ->label('Assign Maintainer')
                        ->icon('heroicon-o-user')
                        ->form(fn (Achievement $record) => AchievementResource::buildMaintainerForm($record))
                        ->action(function (Achievement $record, array $data): void {
                            AchievementResource::handleSetMaintainer($record, $data);

                            Notification::make()
                                ->title('Success')
                                ->body('Successfully assigned maintainer to selected achievement.')
                                ->success()
                                ->send();
                        })
                        ->visible(fn () => $user->can('assignMaintainer', Achievement::class)),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('flags-core')
                        ->label('Promote selected')
                        ->icon('heroicon-o-arrow-up-right')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) use ($user) {
                            $records->each(function (Achievement $record) use ($user) {
                                if (!$user->can('updateField', [$record, 'Flags'])) {
                                    return;
                                }

                                $record->Flags = AchievementFlag::OfficialCore->value;
                                $record->save();
                            });

                            Notification::make()
                                ->title('Success')
                                ->body('Successfully promoted selected achievements.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('flags-unofficial')
                        ->label('Demote selected')
                        ->icon('heroicon-o-arrow-down-right')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) use ($user) {
                            $records->each(function (Achievement $record) use ($user) {
                                if (!$user->can('updateField', [$record, 'Flags'])) {
                                    return;
                                }

                                $record->Flags = AchievementFlag::Unofficial->value;
                                $record->save();
                            });

                            Notification::make()
                                ->title('Success')
                                ->body('Successfully demoted selected achievements.')
                                ->success()
                                ->send();
                        }),
                ])
                    ->label('Bulk promote or demote')
                    ->visible(fn (): bool => $user->can('updateField', [Achievement::class, null, 'Flags'])),

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('type-progression')
                        ->label('Set selected to Progression')
                        ->color('info')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) use ($user) {
                            $records->each(function (Achievement $record) use ($user) {
                                if (!$user->can('updateField', [$record, 'type'])) {
                                    return;
                                }

                                $record->type = AchievementType::Progression;
                                $record->save();
                            });

                            Notification::make()
                                ->title('Success')
                                ->body('Successfully set selected achievements to Progression.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('type-win-condition')
                        ->label('Set selected to Win Condition')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) use ($user) {
                            $records->each(function (Achievement $record) use ($user) {
                                if (!$user->can('updateField', [$record, 'type'])) {
                                    return;
                                }

                                $record->type = AchievementType::WinCondition;
                                $record->save();
                            });

                            Notification::make()
                                ->title('Success')
                                ->body('Successfully set selected achievements to Win Condition.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('type-missable')
                        ->label('Set selected to Missable')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) use ($user) {
                            $records->each(function (Achievement $record) use ($user) {
                                if (!$user->can('updateField', [$record, 'type'])) {
                                    return;
                                }

                                $record->type = AchievementType::Missable;
                                $record->save();
                            });

                            Notification::make()
                                ->title('Success')
                                ->body('Successfully set selected achievements to Missable.')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\BulkAction::make('type-null')
                        ->label('Remove type from selected')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) use ($user) {
                            $records->each(function (Achievement $record) use ($user) {
                                if (!$user->can('updateField', [$record, 'type'])) {
                                    return;
                                }

                                $record->type = null;
                                $record->save();
                            });

                            Notification::make()
                                ->title('Success')
                                ->body('Successfully removed type from selected achievements.')
                                ->success()
                                ->send();
                        }),
                ])
                    ->label('Bulk set type')
                    ->visible(function ($record) use ($user) {
                        if ($this->getOwnerRecord()->system->id === System::Events) {
                            return false;
                        }

                        return $user->can('updateField', [Achievement::class, null, 'type']);
                    }),

                Tables\Actions\BulkAction::make('add-credit')
                    ->label('Bulk add credit')
                    ->modalHeading('Bulk add credit')
                    ->color('gray')
                    ->form(AchievementAuthorshipCreditFormSchema::getSchema())
                    ->action(function (Collection $records, array $data) use ($user) {
                        if (!$user->can('create', [AchievementAuthor::class])) {
                            return false;
                        }

                        $targetUser = User::find($data['user_id']);
                        $task = AchievementAuthorTask::from($data['task']);
                        $backdate = Carbon::parse($data['created_at']);

                        // Load all existing credit records in a single query.
                        $existingRecords = AchievementAuthor::withTrashed()
                            ->whereIn('achievement_id', $records->pluck('id'))
                            ->whereUserId($targetUser->id)
                            ->whereTask($task->value)
                            ->get()
                            ->keyBy('achievement_id');

                        $records->each(function (Achievement $record) use ($existingRecords, $targetUser, $task, $backdate) {
                            $existingRecord = $existingRecords->get($record->id);

                            if ($existingRecord) {
                                if ($existingRecord->trashed()) {
                                    $existingRecord->restore();
                                }
                                $existingRecord->created_at = $backdate;
                                $existingRecord->save();

                                return;
                            }

                            // If no existing credit record is found, create a new one.
                            $record->ensureAuthorshipCredit($targetUser, $task, $backdate);
                        });

                        Notification::make()
                            ->title('Success')
                            ->body('Successfully added credit to selected achievements.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (): bool => $user->can('create', [AchievementAuthor::class])),

                Tables\Actions\BulkAction::make('set-maintainer')
                    ->label('Assign maintainer')
                    ->color('gray')
                    ->form(fn (Achievement $record) => AchievementResource::buildMaintainerForm($record))
                    ->action(function (Collection $records, array $data) {
                        $records->each(function (Achievement $record) use ($data) {
                            AchievementResource::handleSetMaintainer($record, $data);
                        });

                        Notification::make()
                            ->title('Success')
                            ->body('Successfully assigned maintainer to selected achievements.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (): bool => $user->can('assignMaintainer', [Achievement::class])),
            ])
            ->recordUrl(function (Achievement $record): string {
                /** @var User $user */
                $user = Auth::user();

                if ($user->can('update', $record)) {
                    return route('filament.admin.resources.achievements.edit', ['record' => $record]);
                }

                return route('filament.admin.resources.achievements.view', ['record' => $record]);
            })
            ->paginated([50, 100, 150])
            ->defaultPaginationPageOption(50)
            ->defaultSort(function (Builder $query): Builder {
                return $query
                    ->orderBy('DisplayOrder')
                    ->orderBy('DateCreated', 'asc');
            })
            ->reorderRecordsTriggerAction(
                fn (Action $action, bool $isReordering) => $action
                    ->button()
                    ->label($isReordering ? 'Stop reordering' : 'Start reordering'),
            )
            ->reorderable('DisplayOrder', $this->canReorderAchievements())
            ->checkIfRecordIsSelectableUsing(
                fn (Model $record): bool => $user->can('update', $record->loadMissing('game')),
            );
    }

    public function reorderTable(array $order): void
    {
        parent::reorderTable($order);

        /** @var User $user */
        $user = Auth::user();
        /** @var Game $game */
        $game = $this->getOwnerRecord();

        // We don't want to flood the logs with reordering activity.
        // We'll throttle these events by 10 minutes.
        $recentReorderingActivity = DB::table('audit_log')
            ->where('causer_id', $user->id)
            ->where('subject_id', $game->id)
            ->where('subject_type', 'game')
            ->where('event', 'reorderedAchievements')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->first();

        // If the user didn't recently reorder achievements, write a new log.
        if (!$recentReorderingActivity) {
            activity()
                ->useLog('default')
                ->causedBy($user)
                ->performedOn($game)
                ->event('reorderedAchievements')
                ->log('Reordered Achievements');
        }

        // Double write to achievement_set_achievements to ensure it remains in sync.
        $firstAchievementId = (int) $order[0];
        $firstAchievement = Achievement::find($firstAchievementId);
        (new SyncAchievementSetOrderColumnsFromDisplayOrdersAction())->execute($firstAchievement);
    }

    private function canReorderAchievements(): bool
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var Game $game */
        $game = $this->getOwnerRecord();

        return $user->can('update', $game);
    }
}
