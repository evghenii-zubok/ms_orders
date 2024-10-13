<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\ConsoleOutput;

class AppInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inizialize application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->output = new ConsoleOutput();
        $this->info('------------------------------------------------');
        $this->info('-       Starting app post deploy script        -');
        $this->info('------------------------------------------------');
        $this->info('');
        sleep(1);

        $this->info('- Migrating DataBase');
        Artisan::call("migrate");
        $this->comment(Artisan::output());
        sleep(1);

        $this->info('- Composer Update');
        $output = shell_exec("yes | composer update 2>&1");
        $this->comment($output);
        $this->info('');
        sleep(1);

        $this->info('- Cache Clear');
        Artisan::call("cache:clear");
        $this->comment(Artisan::output());
        $this->info('');
        sleep(1);

        $this->info('- Route Clear');
        Artisan::call("route:clear");
        $this->comment(Artisan::output());
        $this->info('');
        sleep(1);

        $this->info('- View Clear');
        Artisan::call("view:clear");
        $this->comment(Artisan::output());
        $this->info('');
        sleep(1);

        $this->info('- Config Clear');
        Artisan::call("config:clear");
        $this->comment(Artisan::output());
        $this->info('');
        sleep(1);

        $this->info('- Create Swagger Client');
        $client = new User();
        $client->client_id = 'Bob Lee';
        $client->client_secret = 'MySuperSecretPassword';
        $client->save();
        $this->info('- Client "Bob Lee" was created and can be used in Swagger Docs.');
        $this->info('');
        sleep(1);

        $this->info('------------------------------------------------');
        $this->info('-                     DONE                     -');
        $this->info('------------------------------------------------');
        $this->info('');
    }
}
