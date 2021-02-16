<?php

namespace Proximum\Vimeet\Application\Command\Event\PracticalInfo;

use Proximum\Vimeet\Domain\Model;

class Update
{
    /**
     * @var Model\Event
     */
    public $event;

    /**
     * @var string
     */
    public $organiserName;

    /**
     * @var string
     */
    public $organiserEmail;

    /**
     * @var string
     */
    public $contactFirstName;

    /**
     * @var string
     */
    public $contactLastName;

    /**
     * @var string
     */
    public $organiserPhone;

    /**
     * @var string
     */
    public $organiserWebsite;

    /**
     * Update constructor.
     *
     * @param Model\Event $event
     */
    public function __construct(Model\Event $event)
    {
        $this->event            = $event;
        $this->organiserName    = $event->getOrganiserName();
        $this->organiserEmail   = $event->getOrganiserEmail();
        $this->contactFirstName = $event->getConfiguration()->getContactFirstName();
        $this->contactLastName  = $event->getConfiguration()->getContactLastName();
        $this->organiserPhone   = $event->getConfiguration()->getOrganiserPhone();
        $this->organiserWebsite = $event->getConfiguration()->getOrganiserWebsite();
    }
}
