<?php

declare(strict_types=1);

namespace Carton\Carton;

use Carton\Carton\Listeners\MergeCartsAfterLogin;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class CartonServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('carton')
            ->hasConfigFile()
            ->hasMigrations(
                'create_carts_table',
                'create_cart_lines_table'
            )
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToStarRepoOnGitHub('pajaeu/carton');
            });
    }

    public function packageBooted(): void
    {
        Event::listen(Authenticated::class, MergeCartsAfterLogin::class);
    }
}
