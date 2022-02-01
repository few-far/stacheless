<?php

namespace FewFar\Stacheless\Commands;

use FewFar\Stacheless\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MakeMigrationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stacheless:migrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new command instance.
     *
     * @param \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $timestamp = date('Y_m_d_His');
        $config = app(Config::class);
        $prefix = $config->get('table_prefix');
        $migration_path = __DIR__ . '/../../migrations/';

        $this->info('Outputting migrations to database/migrations/');

        foreach ($this->files->glob($migration_path . '*') as $source) {
            $filename = str_replace('statamic_', $prefix, Str::after($source, $migration_path));
            $target = base_path("database/migrations/{$timestamp}_" . $filename);

            if ($this->files->exists($target)) {
                continue;
            }

            $this->info('↳ ' . $filename);

            $this->files->copy($source, $target);
        }


        return 0;
    }
}
