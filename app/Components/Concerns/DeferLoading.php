<?php

declare(strict_types=1);

namespace App\Components\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait DeferLoading
{
    public bool $defer = false;

    public bool $ready = false;

    public function ready(): void
    {
        $this->ready = true;
    }

    /**
     * @return array|LengthAwarePaginator<Model>|Collection<int, Model>|null
     */
    protected function loadDeferred(): array|LengthAwarePaginator|Collection|null
    {
        if ($this->defer && !$this->ready) {
            return null;
        }

        if (!method_exists($this, 'load')) {
            return null;
        }

        return $this->load();
    }
}
