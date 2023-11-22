<?php

namespace FewFar\Stacheless\Cms\Mappers\Concerns;

use Illuminate\Support\Arr;

trait ModelsPageMeta
{
    protected string $pageTitleSeparator = "–";

    public function makePageMetaTitle()
    {
        return (
            $this->get($this->values, 'seo_title')
            ?: collect([ $this->get($this->values, 'title'), config('app.name') ])
                ->filter()
                ->implode(' ' . $this->pageTitleSepartor . ' ')
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
