<?php

namespace Carton\Carton\Commands;

use Illuminate\Console\Command;

class CartonCommand extends Command
{
    public $signature = 'carton';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
