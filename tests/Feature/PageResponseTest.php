<?php

use FewFar\Stacheless\Cms\Mappers\Context;
use Illuminate\Support\Facades\View;
use Statamic\Globals\AugmentedVariables;

class PageMapper extends \FewFar\Stacheless\Cms\Mappers\PageMapper
{
    public function makePageModel()
    {
        return [];
    }
}

class PageResponse extends \FewFar\Stacheless\Cms\PageResponse
{
    protected $mapper = PageMapper::class;

    public function makeDefaultContext()
    {
        return new Context([
            'settings' => new AugmentedVariables(collect()),
            'entry' => null,
            'values' => [],
        ]);
    }
}

describe('PageResponse view', function () {
    it('renders a view', function () {
        View::shouldReceive('make')
            ->once()
            ->andReturn($expected = mock(\Illuminate\View\View::class));

        /** @var \Tests\TestCase $this */
        $view = (new PageResponse())->makeView();

        expect($view)->toBe($expected);
    });
});
