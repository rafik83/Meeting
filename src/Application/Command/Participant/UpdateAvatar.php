<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;

class UpdateAvatar
{
    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var User
     */
    public $user;

    /**
     * @param TemplateData $templateData
     * @param Participant  $participant
     * @param string       $locale
     * @param User         $user
     */
    public function __construct(TemplateData $templateData, Participant $participant, $locale, User $user)
    {
        $this->templateData = $templateData;
        $this->participant  = $participant;
        $this->locale       = $locale;
        $this->user         = $user;
    }
}
