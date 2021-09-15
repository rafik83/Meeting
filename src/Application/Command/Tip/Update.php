<?php

namespace Proximum\Vimeet\Application\Command\Tip;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Tip\Tip;

class Update implements Command
{
    /** @var Tip */
    public $tip;

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

    public function __construct(Tip $tip)
    {
        $this->tip                 = $tip;
        $this->title               = $tip->getTitle();
        $this->onMeetingManagement = $tip->isOnMeetingManagement();
        $this->onPrintPlanning     = $tip->isOnPrintPlanning();
        $this->onCatalog           = $tip->isOnCatalog();
        $this->onSheet             = $tip->isOnSheet();
        $this->onAgenda            = $tip->isOnAgenda();
        $this->onPackage           = $tip->isOnPackage();
        $this->onProgram           = $tip->isOnProgram();
        $this->onContacts          = $tip->isOnContacts();
        $this->onConfirmationPhone = $tip->isOnConfirmationPhone();
        $this->onNetworking        = $tip->isOnNetworking();

        foreach ($this->tip->getTranslations() as $translation) {
            $this->translations[$translation->getLocale()] = [
                'title' => $translation->getTitle(),
                'content' => $translation->getContent(),
                'locale' => $translation->getLocale(),
            ];
        }
    }
}
