<?php

namespace Proximum\Vimeet\Application\Command\User;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;

class Participate
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var Type
     */
    public $type;

    /**
     * @var array
     */
    public $data;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * @var Participant
     */
    public $participant;

    /**
     * @var string
     */
    public $locale;

    /**
     * @var bool
     */
    public $owner;

    /**
     * @var TemplateData
     */
    public $templateData;

    /**
     * @param User         $user
     * @param Event        $event
     * @param Type         $type
     * @param string       $locale
     * @param array        $data
     * @param TemplateData $templateData
     */
    public function __construct(User $user, Event $event, Type $type, $locale, array $data, TemplateData $templateData)
    {
        $this->user         = $user;
        $this->event        = $event;
        $this->type         = $type;
        $this->locale       = $locale;
        $this->data         = $data;
        $this->owner        = true;
        $this->templateData = $templateData;
    }
}
