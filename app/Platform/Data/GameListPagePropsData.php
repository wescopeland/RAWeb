<?php

declare(strict_types=1);

namespace App\Platform\Data;

use App\Data\PaginatedData;
use App\Data\UserData;
use App\Data\UserPermissionsData;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\LiteralTypeScriptType;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript('GameListPageProps<TItems = App.Platform.Data.GameListEntry>')]
class GameListPagePropsData extends Data
{
    /**
     * @param SystemData[] $filterableSystemOptions
     */
    public function __construct(
        public PaginatedData $paginatedGameListEntries,
        public array $filterableSystemOptions,
        public UserPermissionsData $can,
        public string $persistenceCookieName,
        #[LiteralTypeScriptType('Record<string, any> | null')]
        public ?array $persistedViewPreferences = null,
        public int $defaultDesktopPageSize = 25,
        public ?UserData $targetUser = null,
        public ?UserSetRequestInfoData $userRequestInfo = null,
    ) {
    }
}
