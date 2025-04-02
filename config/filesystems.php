<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        'media' => [
            'driver' => 'local',
            'root' => storage_path('app/media'),
            'url' => env('MEDIA_URL', env('APP_URL') . '/media'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'static' => [
            'driver' => 'local',
            'root' => storage_path('app/static'),
            'url' => env('ASSET_URL'),
            'visibility' => 'public',
            'throw' => false,
        ],

        'livewire-tmp' => [
            'driver' => 'local',
            'root' => storage_path('app/livewire-tmp'),
            'url' => env('APP_URL') . '/storage/livewire-tmp',
            'visibility' => 'private',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'options' => [
                'CacheControl' => 'max-age=2628000, no-transform, public',
            ],
            'minio' => env('APP_ENV') === 'local' && env('LARAVEL_SAIL') ? true : env('AWS_MINIO', env('AWS_USE_PATH_STYLE_ENDPOINT', false)),
            'report' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),

        storage_path('app/static/assets') => public_path('assets'),

        storage_path('app/static/vendor') => public_path('vendor'),

        storage_path('app/static/docs') => base_path('docs/dist'),

        public_path('Badge') => storage_path('app/media/Badge'),

        public_path('Images') => storage_path('app/media/Images'),

        public_path('bin') => storage_path('app/media/bin'),

        public_path('UserPic') => storage_path('app/media/UserPic'),
    ],

];
