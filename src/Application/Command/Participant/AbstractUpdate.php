<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;

abstract class AbstractUpdate
{
    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * @var Participant|null
     */
    public $participant;

    /**
     * @var array
     */
    public $data;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var User
     */
    public $user;

    /**
     * @param TemplateData     $templateData
     * @param Participant|null $participant
     * @param string           $locale
     * @param array            $data
     * @param User             $user
     */
    public function __construct(TemplateData $templateData, Participant $participant = null, $locale, $data, User $user)
    {
        $this->templateData = $templateData;
        $this->participant  = $participant;
        $this->locale       = $locale;
        $this->data         = $data;
        $this->user         = $user;
    }
}
