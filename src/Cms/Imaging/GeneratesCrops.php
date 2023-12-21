<?php

namespace FewFar\Stacheless\Cms\Imaging;

use Illuminate\Support\Enumerable;
use Statamic\Contracts\Assets\Asset;

interface GeneratesCrops
{
    /**
     * Checks that Asset is a bitmap image.
     */
    public function canGenerateCrops(?Asset $asset) : bool;

    /**
     * Gets all available crop widths for Asset.
     *
     * @return Enumerable<int>
     */
    public function crops(?Asset $asset) : Enumerable;

    /**
     * Generates all image crops for the frontend Image.vue class.
     *
     * @return Enumerable<string>
     */
    public function generateCrops(?Asset $asset) : Enumerable;

    /**
     * Generates single image crop for the frontend Image.vue class.
     */
    public function generateCrop(?Asset $asset, $width) : ?string;

    /**
     * Generates the model needed for the frontend to use the crops.
     */
    public function model(Asset $asset) : Enumerable;
}
