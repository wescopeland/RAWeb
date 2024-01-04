@props([
    'filterOptions' => [],
])

<label class="text-xs font-bold">Filters</label>
<div class="flex gap-x-4 text-2xs gap-y-0.5">
    <div class="flex flex-col">
        <x-game.related-games-meta-panel.filter-checkbox
            kind="console"
            :isPreChecked="$filterOptions['console']"
        >
            Group by console
        </x-game.related-games-meta-panel.filter-checkbox>

        <x-game.related-games-meta-panel.filter-checkbox
            kind="populated"
            :isPreChecked="$filterOptions['populated']"
        >
            Only with achievements
        </x-game.related-games-meta-panel.filter-checkbox>
    </div>
</div>