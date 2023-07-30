<?php

namespace FewFar\Stacheless\Commands;

use Illuminate\Console\Command;
use Statamic\Auth\Eloquent\User as EloquentUser;
use Statamic\Auth\File\User as FileUser;
use Statamic\Auth\UserRepositoryManager;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Auth\UserRepository as UserRepositoryContract;
use Statamic\Facades\User;
use Statamic\Stache\Repositories\UserRepository as FileRepository;

class MigrateUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stacheless:migrate:users';

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
    public function handle()
    {
        if (config('statamic.users.repository') !== 'eloquent') {
            $this->error('Your site is not using the eloquent user repository.');

            return Command::FAILURE;
        }

        app()->bind(UserContract::class, FileUser::class);
        app()->bind(UserRepositoryContract::class, FileRepository::class);

        $users = User::all();

        app()->bind(UserContract::class, EloquentUser::class);

        $repository = app(UserRepositoryManager::class)->createEloquentDriver([]);

        $this->info('Creating or Updating content in DB from Statamic Stache Stores.');

        foreach ($users as $user) {
            $this->info('↳ ' . $user->id() . ' ' . $user->email());

            $data = $user->data();

            $eloquentUser = $repository->find($user->id()) ?? $repository->make()
                ->email($user->email())
                ->preferences($user->preferences())
                ->data($data->except(['groups', 'roles'])->merge(['name' => $user->name()]))
                ->id($user->id());

            if ($user->isSuper()) {
                $eloquentUser->makeSuper();
            }

            if (count($data->get('groups', [])) > 0) {
                $eloquentUser->groups($data->get('groups'));
            }

            if (count($data->get('roles', [])) > 0) {
                $eloquentUser->roles($data->get('roles'));
            }

            $eloquentUser->saveToDatabase();

            $eloquentUser->model()->forceFill(['password' => $user->password()]);
            $eloquentUser->model()->saveQuietly();
        }

        return Command::SUCCESS;
    }
}
