<?php

namespace FewFar\Stacheless\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MakeCpPublicVendorSymlinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stacheless:make:cp-link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates /public/vendor/stacheless folder symlink for cp resources.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $link = public_path('vendor/stacheless');

        if (File::exists($link)) {
            $this->info('Symlink already exists at: ' . $link);

            return Command::SUCCESS;
        }

        $process = new Process(['ln', '-s', '../../vendor/few-far/stacheless/dist/public/', $link]);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->error('Unable to create symlink at: '. $link);

            return Command::FAILURE;
        }

        $this->info('Symlink created at: ' . $link);

        return Command::SUCCESS;
    }
}
