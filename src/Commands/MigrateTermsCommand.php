<?php

namespace FewFar\Stacheless\Commands;

use FewFar\Stacheless\Repositories\TermRepository;
use Illuminate\Console\Command;

class MigrateTermsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stacheless:migrate:terms';

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
    public function handle(TermRepository $repository)
    {
        $store = \Statamic\Facades\Stache::store('terms');

        $this->info('Updating or creating content in DB from Statamic Stache Stores.');

        foreach ($store->discoverStores() as $store) {
            foreach ($store->getItemsFromFiles() as $term) {
                $this->info('↳ ' . $term->id() . ' - ' . $term->title());

                $repository->save($term->term());
            }
        }

        return Command::SUCCESS;
    }
}
