<?php

namespace FewFar\Stacheless\Cms\Mappers;

use FewFar\Stacheless\Cms\Support\Concerns\BuildsModels;
use FewFar\Stacheless\Repositories\NavigationTreeRepository;
use Illuminate\Support\Arr;
use Statamic\Facades\Entry;

class NavigationMapper
{
    use BuildsModels;

    public function mapHeaderNavItem($page)
    {
        return collect($this->mapNavItem($page))
            ->merge([
                'icon' => Arr::get($page, 'data.icon'),
                'description' => ($description = trim(Arr::get($page, 'data.description')))
                    ? [ 'text' => $description ]
                    : null,
            ])
            ->filter()
            ->all();
    }

    public function mapNavItem($page)
    {
        return [
            'link' => [
                'href' => Arr::get($page, 'url') ?: optional(Entry::find(Arr::get($page, 'data.page')))->url(),
                'target' => Arr::get($page, 'data.open_in_new_tab') === true ? '_blank' : null,
            ],
            'copy' => [ 'text' => Arr::get($page, 'title') ],
        ];
    }

    public function model()
    {
        $navs = $this->findNavs();

        return $this->mapNavTrees($navs);
    }

    public function findNavs()
    {
        return app(NavigationTreeRepository::class)->all();
    }

    public function mapNavTrees($navs)
    {
        return collect($navs)
            ->keyBy->handle()
            ->filter(fn ($tree) => $tree->structure())
            ->map(fn ($tree) => $this->mapNavTree($tree));
    }

    public function mapNavTree($tree)
    {
        return collect($tree->tree())
            ->map(function ($page) use ($tree) {

                $model = $tree->handle() === 'header'
                    ? $this->mapHeaderNavItem($page)
                    : $this->mapNavItem($page);

                foreach ((Arr::get($page, 'children') ?? []) as $child) {
                    $model['children'][] = $this->mapNavItem($child);
                }

                return $model;
            });
    }
}
