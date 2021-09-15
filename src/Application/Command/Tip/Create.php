<?php

namespace Proximum\Vimeet\Application\Command\Tip;

use Proximum\Vimeet\Application\Command\Command;

class Create implements Command
{
    /** @var string */
    public $title;

    /** @var bool */
    public $onMeetingManagement;

    /** @var bool */
    public $onCatalog;

    /** @var bool */
    public $onPrintPlanning;

    /** @var bool */
    public $onSheet;

    /** @var bool */
    public $onProgram;

    /** @var bool */
    public $onAgenda;

    /** @var bool */
    public $onPackage;

    /** @var bool */
    public $onContacts;

    /** @var bool */
    public $onConfirmationPhone;

    /** @var bool */
    public $onNetworking;

    /** @var array */
    public $translations;

    /**
     * Create constructor.
     *
     * @param array $defaultLocales
     */
    public function __construct(array $defaultLocales)
    {
        foreach ($defaultLocales as $locale) {
            $this->translations[$locale] = ['locale' => $locale];
        }
    }
}
