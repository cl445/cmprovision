<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;


class AddService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
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
    public function handle(): int
    {
        $name = $this->argument('name');
        $url = $this->argument('url');

        $services = Config::get('services.external_services');

        $services[$name] = $url;

        Config::set('services.external_services', $services);
        Config::save();

        $this->info('Service added successfully!');
        return 0;
    }
}
