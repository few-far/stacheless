<?php

namespace FewFar\Stacheless\Cms\Mappers\Concerns;

use Illuminate\Support\Collection;

trait ModelsBreadcrumb
{
    /**
     * Enable to skip the home (root) of the current chain.
     */
    protected $breadcrumbSkipHome = false;

    /**
     * Enable to exclude the current page from the breadcrumb.
     */
    protected $breadcrumbSkipCurrent = false;

    /**
     * Creates a list of breadcrumbs from the current page.
     *
     * @return Collection<array{copy: array, link: array}>
     */
    public function makeBreadcrumb() : array
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
}
