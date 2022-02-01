<?php

namespace FewFar\Stacheless\Commands;

use FewFar\Stacheless\Repositories\CollectionTreeRepository;
use Illuminate\Console\Command;

class MigrateCollectionTreesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stacheless:collection-trees';

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
    public function handle()
    {
        $repository = app(CollectionTreeRepository::class);
        $store = app(config('statamic.stache.stores.collection-trees.class'));

        $this->info('Creating or Updating content in DB from Statamic Stache Stores.');

        foreach ($store->getItemsFromFiles() as $item) {
            $this->info($item->handle());
            $repository->save($item);
        }

        return 0;
    }
}
