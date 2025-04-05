<?php

declare(strict_types=1);

namespace App\Http;

use App\Support\Concerns\HandlesResources;
use App\Support\Concerns\ResolvesSlugs;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use HandlesResources;
    use ResolvesSlugs;
}
