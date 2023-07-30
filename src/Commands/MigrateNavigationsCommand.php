<?php

namespace FewFar\Stacheless\Commands;

use FewFar\Stacheless\Repositories\NavigationRepository;
use Illuminate\Console\Command;

class MigrateNavigationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stacheless:migrate:navigations';

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
    public function handle(NavigationRepository $repository)
    {
        $store = \Statamic\Facades\Stache::store('navigation');

        $this->info('Creating or Updating content in DB from Statamic Stache Stores.');

        foreach ($store->getItemsFromFiles() as $item) {
            $this->info($item->handle());
            $repository->save($item);
        }

        return Command::SUCCESS;
    }
}
