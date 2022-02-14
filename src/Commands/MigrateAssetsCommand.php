<?php

namespace FewFar\Stacheless\Commands;

use FewFar\Stacheless\Repositories\AssetRepository;
use Illuminate\Console\Command;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Assets\Asset as StacheAsset;

class MigrateAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stacheless:assets';

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
        $repository = app(AssetRepository::class);
        $store = app(config('statamic.stache.stores.assets.class'));

        $this->info('Creating or Updating content in DB from Statamic Stache Stores.');

        // Revert to default Stache Type when loading from Stache Store.
        $this->laravel->bind(AssetContract::class, StacheAsset::class);
        $items = $store->getItemsFromFiles();

        // Reset back to our override:
        $this->laravel->bind(AssetContract::class, $repository->bindings()[AssetContract::class]);

        foreach ($items as $item) {
            $this->info('↳ ' . $item->id());

            $item->hydrate();

            $repository->save(
                $repository
                    ->make()
                    ->container($item->container())
                    ->path($item->path())
                    ->setMeta($item->meta())
                    ->syncOriginal()
            );
        }

        return 0;
    }
}
