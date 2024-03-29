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
    protected $signature = 'stacheless:migrate:entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates stache (file) based content to configured Stacheless Driver (DB).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(EntryRepository $repository)
    {
        $store = \Statamic\Facades\Stache::store('entries');

        $this->info('Updating or creating content in DB from Statamic Stache Stores.');

        foreach ($store->discoverStores() as $store) {
            foreach ($store->getItemsFromFiles() as $entry) {
                $this->info('↳ ' . $entry->id() . ' - ' . $entry->uri());

                $repository->save($entry);
            }
        }

        return Command::SUCCESS;
    }
}
