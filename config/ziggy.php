<?php

/**
 * @see https://github.com/tighten/ziggy?tab=readme-ov-file#filtering-routes
 */

return [
    'output' => [
        'path' => 'resources/js/types',
    ],
    'except' => [
        'demo.*',
        'debugbar.*',
        'filament.*',
        'horizon.*',
        'livewire.*',
        'log-viewer.*',
        'ignition.*',
        'laravel-folio',
    ],
];
