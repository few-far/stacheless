<?php

namespace FewFar\Stacheless\Cms\Forms\Mail;

use Illuminate\Mail\Mailable;

class Submission extends Mailable
{
    /**
     * The form instance.
     *
     * @var \Statamic\Contracts\Entries\Entry|\Statamic\Entries\Entry
     */
    protected $form;

    /**
     * The submission array.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $submission;

    /**
     * Create a new message instance.
     *
     * @var \Statamic\Contracts\Entries\Entry  $form
     * @var array|\Illuminate\Support\Collection  $submission
     * @return void
     */
    public function __construct($form, $submission)
    {
        $this->form = $form;
        $this->submission = collect($submission);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->markdown('emails.form-submission')
            ->subject('Form Submission: ' . $this->form->get('title'))
            ->with([
                'form' => $this->form,
                'field_rows' => $this->submission['field_rows'],
                'meta_rows' => $this->submission['meta_rows'],
            ]);
    }
}
