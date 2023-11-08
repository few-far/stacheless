<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Statamic\Actions\Action;
use Statamic\Contracts\Assets\Asset;

class GenerateImageSrcsetsAction extends Action
{
    public static function title()
    {
        return __('Regenerate Crops');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function authorize($user, $item)
    {
        return $user->can('store', [Asset::class, $item->container()]);
    }

    public function run($items, $values)
    {
        /** @var GenerateImageSrcsets */
        $generator = app(GenerateImageSrcsets::class);

        foreach ($items->whereInstanceOf(Asset::class) as $asset) {
            GenerateImageSrcsetsJob::dispatchIf($generator->canGenerateCrops($asset), $asset);
        }
    }
}
