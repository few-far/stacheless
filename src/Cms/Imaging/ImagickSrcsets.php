<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Statamic\Contracts\Assets\Asset;

/**
 * Creates crops using the imagemagic CLI.
 */
class ImagickSrcsets implements GeneratesCrops
{
    use CanGenerateCrops;

    public function crops(?Asset $asset) : Enumerable
    {
        if (!$this->canGenerateCrops($asset)) {
            return collect();
        }

        // Most images we're going to generate are 2x DPI (retina) so we will
        // half our available image size when generating crops.

        $width = intval(floor($asset->width() / 2));
        $max = min(2000, $width);

        $crops = collect([500, 1000, 1500, 2000, $max])
            ->reject(fn ($crop) => $crop > $max)
            ->unique()
            ->sort()
            ->values();

        return $crops;
    }

    /**
     * Generates image crops for the frontend Image.vue class.
     */
    public function generateCrop(?Asset $asset, $width) : ?string
    {
        if (!$this->canGenerateCrops($asset)) {
            return null;
        }

        $dimensions = ($width * 2) . 'x';
        $quality = 90;

        $hash = md5(json_encode(compact('dimensions', 'quality')));
        $output_path = storage_path('app/imaging/' . $asset->container()->id() . '/' . $asset->path() . '/' . $hash . '.webp');

        File::ensureDirectoryExists(dirname($output_path));

        $result = Process::run([
            'convert',
            '-resize', $dimensions,
            '-quality', $quality,
            $asset->resolvedPath(),
            $output_path,
        ]);

        $result->throw();

        return $output_path;
    }

    /**
     * Generates the model needed for the frontend to use the crops.
     */
    public function model(Asset $asset) : Collection
    {
        $url = $asset->url();

        return $this->crops($asset)->map(function ($width) use ($url) {
            $dimensions = ($width * 2) . 'x';
            $quality = 90;

            $hash = md5(json_encode(compact('dimensions', 'quality')));

            return [
                'width' => $width,
                'src' => $url . '/' . $hash . '.webp',
            ];
        });
    }
}
