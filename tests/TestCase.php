<?php

namespace TarfinLabs\LaravelConfig\Tests;

use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use TarfinLabs\LaravelConfig\LaravelConfigServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/../database/factories');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        Schema::dropAllTables();

        $this->artisan('migrate', [
            '--database' => 'mysql',
            '--realpath' => realpath(__DIR__.'/../database/migrations'),
        ]);
        $this->artisan('laravel-config:install');

        $this->autoloadFix();
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelConfigServiceProvider::class,
        ];
    }

    protected function autoloadFix(): void
    {
        $dirs_to_load = [
            database_path('factories'),
            app_path('Traits'),
            app_path('Models'),
        ];

        foreach ($dirs_to_load as $dir) {
            if (is_dir($dir)) {
                foreach (glob($dir . '/*.php') as $file) {
                    require_once $file;
                }
            }
        }
    }
}
