<?php

namespace Kiwilan\Steward\Commands;

use Illuminate\Console\Command;
use Kiwilan\Steward\Services\ConverterService;
use Kiwilan\Steward\Services\RouteService;

class RoutePrintCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'route:print';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Print all routes into JSON file.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $list = RouteService::getList();
        ConverterService::saveAsJson($list, 'routes');

        return Command::SUCCESS;
    }
}
