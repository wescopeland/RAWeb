<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use Exception;
use Livewire\Component;

class ActivePlayers extends Component
{
    public $activePlayers = [];
    public $hasError = false;

    public function mount()
    {
        $this->updateActivePlayers();
    }

    public function updateActivePlayers()
    {
        $this->activePlayers = getCachedLatestRichPresenceUpdates();

        try {
            $this->activePlayers = getCachedLatestRichPresenceUpdates();
        } catch (Exception $e) {
            $this->hasError = true;
        }
    }

    public function render()
    {
        return view('livewire.active-players');
    }
}
