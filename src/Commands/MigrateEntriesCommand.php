<?php

namespace FewFar\Stacheless\Commands;

use FewFar\Stacheless\Repositories\EntryRepository;
use Illuminate\Console\Command;

class MigrateEntriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stacheless:entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Stacheless Entry Repository.
     *
     * @var string
     */
    protected $repository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EntryRepository $repo)
    {
        $this->repo = $repo;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $store = app(config('statamic.stache.stores.entries.class'));

        $this->info('Updating or creating content in DB from Statamic Stache Stores.');

        foreach ($store->discoverStores() as $store) {
            foreach ($store->getItemsFromFiles() as $entry) {
                $this->info('↳ ' . $entry->id() . ' - ' . $entry->uri());

                $this->repo->save($entry);
            }
        }

        return 0;
    }
}
