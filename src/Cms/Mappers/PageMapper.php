<?php

namespace FewFar\Stacheless\Cms\Mappers;

use FewFar\Stacheless\Cms\Support\Concerns\BuildsModels;
use FewFar\Stacheless\Cms\Navigation;
use Illuminate\Support\Arr;
use Statamic\Facades\Entry;
use Statamic\Globals\GlobalSet;

abstract class PageMapper
{
    use BuildsModels;
    use Concerns\InteractsWithContext;
    use Concerns\ModelsBlocks;
    use Concerns\ModelsBreadcrumb;

    public function viewModel()
    {
        return [
            'pagemeta' => $this->pageMeta(),
            'model' => $this->model(),
        ];
    }

    abstract public function makePageModel();

    public function model()
    {
        $model = $this->makePageModel();

        return $this->ensurePageModelDefaults($model);
    }

    public function ensurePageModelDefaults($model)
    {
        return array_merge([], $model ?? null, [
            'page' => $model['page'] ?? 'blocks',
            'blocks' => $model['blocks'] ?? $this->makeBlocks(),
        ]);
    }

    public function makeNav()
    {
        return app(Navigation::class)->model();
    }
}
