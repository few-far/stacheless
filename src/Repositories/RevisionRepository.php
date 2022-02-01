<?php

namespace FewFar\Stacheless\Repositories;

use FewFar\Stacheless\Config;
use Illuminate\Support\Carbon;
use Statamic\Contracts\Revisions\Revision as TypeContract;
use Statamic\Contracts\Revisions\RevisionRepository as RepositoryContract;
use Statamic\Facades\YAML;
use Statamic\Revisions\Revision;
use Statamic\Revisions\WorkingCopy;

class RevisionRepository implements RepositoryContract
{
    use Concerns\TypeRepository {
        save as saveType;
    }

    /**
     * Statamic type "slug" used for config and Blink cache.
     *
     * @var string
     */
    protected $typeKey = 'revisions';

    /**
     * Determines which Statamic type this class manages.
     *
     * @var string
     */
    protected $typeClass = TypeContract::class;

    /**
     * Determines if Statamic type has a site/locale.
     *
     * @var bool
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    protected function makeBlinkKey($key)
    {
        return "$this->typeKey::{$key['filename']}::{$key['key']}";
    }

    protected function makeWhereArgs($type)
    {
        return [
            'key' => $type->key(),
            'filename' => ($type instanceof WorkingCopy) ? 'working' : $type->id(),
        ];
    }

    public function whereKey($key)
    {
        return $this->getModelClass()::query()
            ->where('key', $key)
            ->where('filename', '!=', 'working')
            ->get()
            ->map(function ($model) {
                return $this->toType($model);
            })
            ->keyBy(function ($revision) {
                return $revision->date()->timestamp;
            });
    }

    public function findWorkingCopyByKey($key)
    {
        $model = $this->getModelClass::query()
            ->where('key', $key)
            ->where('filename', 'working')
            ->first();

        if (! $model) {
            return null;
        }

        return $this->toType($model);
    }

    public function save(TypeContract $revision)
    {
        $revision->id($revision->date()->timestamp);

        return $this->saveType($revision);
    }

    protected function hydrateType($type, $model)
    {
        $yaml = YAML::parse($model->yaml);

        $type
            ->key($model->key)
            ->action($yaml['action'] ?? false)
            ->id($date = $yaml['date'])
            ->date(Carbon::createFromTimestamp($date))
            ->user($yaml['user'] ?? false)
            ->message($yaml['message'] ?? false)
            ->attributes($yaml['attributes']);
    }
}
