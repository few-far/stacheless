<?php

namespace FewFar\Stacheless\Commands;

use FewFar\Stacheless\Repositories\CollectionRepository;
use Illuminate\Console\Command;

class MigrateCollectionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stacheless:migrate:collections';

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
    public function handle(CollectionRepository $repository)
    {
        $store = \Statamic\Facades\Stache::store('collections');

        $this->info('Creating or Updating content in DB from Statamic Stache Stores.');

        foreach ($store->getItemsFromFiles() as $item) {
            $this->info('↳ ' . $item->handle());
            $repository->save($item);
        }

        return Command::SUCCESS;
    }
}
