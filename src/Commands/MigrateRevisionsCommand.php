<?php

namespace FewFar\Stacheless\Commands;

use FewFar\Stacheless\Repositories\RevisionRepository;
use Illuminate\Console\Command;

class MigrateRevisionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stacheless:migrate:revisions';

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
    public function handle(RevisionRepository $repository, RevisionRepository $stache)
    {
        $store = \Statamic\Facades\Stache::store('entries');

        $this->info('Updating or creating content in DB from Statamic Stache Stores.');

        foreach ($store->discoverStores() as $store) {
            foreach ($store->getItemsFromFiles() as $entry) {
                $this->info($entry->id());

                $key = vsprintf('collections/%s/%s/%s', [
                    $entry->collectionHandle(),
                    $entry->locale(),
                    $entry->id(),
                ]);

                $revisions = collect()
                    ->push($stache->findWorkingCopyByKey($key))
                    ->concat($stache->whereKey($key))
                    ->filter();

                foreach ($revisions as $revision) {
                    $repository->save($revision);
                }
            }
        }

        return Command::SUCCESS;
    }
}
