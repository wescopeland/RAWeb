<?php

use Illuminate\Support\Facades\Blade;

function RenderShortcodeButtons(): void
{
    echo Blade::render('<x-community.shortcode-buttons />');
}
