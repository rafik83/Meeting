<?php

namespace Proximum\Vimeet\Application\Query\Register;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;

class PreFillUserData implements Query
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
     * @var TemplateData
     */
    public $templateData;

    /**
     * @var string
     */
    public $locale;

    /**
     * @param User         $user
     * @param Event        $event
     * @param TemplateData $templateData
     * @param string       $locale
     */
    public function __construct(User $user, Event $event, TemplateData $templateData, string $locale)
    {
        $this->user = $user;
        $this->event = $event;
        $this->templateData = $templateData;
        $this->locale = $locale;
    }
}
