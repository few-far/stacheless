<?php

namespace FewFar\Stacheless\Cms\Forms\Fieldtypes;

use Statamic\Fields\Fieldtype;

class CmsFormSubmissions extends FieldType
{
    protected $categories = ['special'];
    protected $icon = 'entires';

    // public function augment($value)
    // {
    // }

    protected function configFieldItems(): array
    {
        return [];
    }

    /**
     * The blank/default value.
     *
     * @return array
     */
    public function defaultValue()
    {
        return null;
    }

    /**
     * Pre-process the data before it gets sent to the publish page.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function preProcess($data)
    {
        return $data;
    }

    /**
     * Process the data before it gets saved.
     *
     * @param mixed $data
     * @return array|mixed
     */
    public function process($data)
    {
        return $data;
    }

    public function preload()
    {
        $parent = $this->field()->parent();

        if (!$parent instanceof \Statamic\Contracts\Entries\Entry) {
            return [];
        }

        return [
            'action' => route('statamic.cp.app.forms.submissions', $parent->id()),
        ];
    }
}
