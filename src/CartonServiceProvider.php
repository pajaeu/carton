<?php

namespace Carton\Carton;

use Carton\Carton\Commands\CartonCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class CartonServiceProvider extends PackageServiceProvider
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
            ->hasViews()
            ->hasMigration('create_carton_table')
            ->hasCommand(CartonCommand::class);
    }
}
