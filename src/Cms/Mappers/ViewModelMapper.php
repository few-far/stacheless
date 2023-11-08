<?php

namespace FewFar\Stacheless\Cms\Mappers;

use FewFar\Stacheless\Cms\Support\Concerns\BuildsModels;
use FewFar\Stacheless\Cms\Navigation;
use Illuminate\Support\Arr;
use Statamic\Facades\Entry;

abstract class ViewModelMapper
{
    use BuildsModels;

    public function viewModel()
    {
        return [
            'pagemeta' => $this->pageMeta(),
            'model' => $this->model(),
        ];
    }

    protected $block_mapper = BlockMapper::class;
    protected $blocks_attribute = 'blocks';
    protected $blocks_prepend = null;
    protected $blocks_append = null;

    abstract public function makePageModel();

    public function model()
    {
        $model = $this->makePageModel();

        return $this->ensurePageModelDefaults($model);
    }

    public function ensurePageModelDefaults($model)
    {
        return array_merge($model ?? null, [
            'page' => $model['page'] ?? 'blocks',
            'blocks' => $model['blocks'] ?? $this->makeBlocks(),
        ]);
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

    public function makeNav()
    {
        return app(Navigation::class)->model();
    }

    protected $breadcrumbSkipHome = false;
    protected $breadcrumbSkipCurrent = false;

    public function makeBreadcrumb()
    {

        $pages = [
            $this->breadcrumbSkipCurrent ? null : [
                'copy' => $this->makeText($this->values, 'title'),
                'link' => [ 'href' => $this->get($this->values, 'url') ],
            ],
        ];

        $page = $this->get($this->values, 'parent') ?? $this->get($this->values, 'collection')?->mount()?->page();

        while ($page) {
            if ($this->breadcrumbSkipHome && $page->isRoot()) {
                break;
            }

            $entry = $page->entry();

            if (!$entry) {
                break;
            }

            $pages[] = [
                'copy' => ['text' => $entry->get('title')],
                'link' => ['href' => $entry->url()],
            ];

            $page = $page->parent();
        }

        return collect($pages)->reverse()->filter()->values()->all();
    }

    public function makeBlocks()
    {
        $blocks = $this->makeBlocksForAttribute($this->blocks_attribute);

        $prepend = $this->prependBlocks();
        $append = $this->appendBlocks();

        return collect([ $prepend, $blocks, $append ])
            ->flatten(1)
            ->filter()
            ->values();
    }

    public function blockMapper()
    {
        return app($this->block_mapper)
            ->setContext($this->context);
    }

    public function makeBlocksForAttribute($attribute)
    {
        return $this->blockMapper()->map($this->get($this->values, $attribute));
    }

    public function prependBlocks()
    {
        return $this->blocks_prepend;
    }

    public function appendBlocks()
    {
        return $this->blocks_append;
    }

    public function setAppendBlocks($blocks)
    {
        $this->blocks_append = $blocks;

        return $this;
    }

    public function setPrependBlocks($blocks)
    {
        $this->blocks_prepend = $blocks;

        return $this;
    }

    protected $context;

    public function setContext($context)
    {
        $this->context = collect($context)->all();

        return $this;
    }

    public function __get($key)
    {
        return ($this->context)[$key] ?? null;
    }

    public function makePageMetaTitle()
    {
        return (
            $this->get($this->values, 'seo_title')
            ?: collect([ $this->get($this->values, 'title'), config('app.name') ])->filter()->implode(' – ')
        );
    }

    public function pageMeta()
    {
        return [
            'title' => $this->makePageMetaTitle(),
            'description' => (
                $this->get($this->values, 'seo_description')
                ?: $this->get($this->settings, 'seo_description')
                ?: ''
            ),
            'social_image' => (
                Arr::get($this->makeAsset($this->values, 'seo_image'), 'image.src')
                ?: Arr::get($this->makeAsset($this->settings, 'seo_image'), 'image.src')
                ?: url('/static/images/og.png')
            ),
            'url' => request()->url(),
            'noindex' => $this->get($this->values, 'seo_noindex'),
        ];
    }
}
