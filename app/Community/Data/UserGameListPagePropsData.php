<?php

declare(strict_types=1);

namespace App\Community\Data;

use App\Data\PaginatedData;
use App\Platform\Data\SystemData;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript('UserGameListPageProps<TItems = App.Community.Data.UserGameListEntry>')]
class UserGameListPagePropsData extends Data
{
    /**
     * @param SystemData[] $filterableSystemOptions
     */
    public function __construct(
        public PaginatedData $paginatedGameListEntries,
        public array $filterableSystemOptions,
    ) {
    }
}
