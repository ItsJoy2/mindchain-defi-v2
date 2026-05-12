<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    protected $signature = 'make:service {name}';
    protected $description = 'Create a new service class';

    public function handle()
    {
        $name = $this->argument('name');

        $path = app_path("Services/{$name}.php");

        if (File::exists($path)) {
            $this->error("Service already exists!");
            return;
        }

        if (!File::exists(app_path('Services'))) {
            File::makeDirectory(app_path('Services'));
        }

        $template = <<<PHP
<?php

namespace App\Services;

class {$name}
{
    //
}
PHP;

        File::put($path, $template);

        $this->info("Service {$name} created successfully!");
    }
}
