<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;

class PreUpdate
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var array
     */
    public $data;

    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * PreUpdate constructor.
     *
     * @param User         $user
     * @param Participant  $participant
     * @param Event        $event
     * @param array        $data
     * @param TemplateData $templateData
     * @param string       $locale
     */
    public function __construct(
        User $user,
        Participant $participant,
        Event $event,
        array $data,
        TemplateData $templateData,
        string $locale
    ) {
        $this->user         = $user;
        $this->event        = $event;
        $this->locale       = $locale;
        $this->participant  = $participant;
        $this->data         = $data;
        $this->templateData = $templateData;
    }
}
