<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Event;

class CreateMessage implements Command
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $emailTemplate;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $subject;

    /**
     * @var bool
     */
    public $sendToEmailTeam = false;

    /**
     * @var bool
     */
    public $sendEmailToBillingInfo = false;

    /**
     * Create constructor.
     *
     * @param Event  $event
     * @param string $name
     * @param string $subject
     * @param string $emailTemplate
     * @param bool   $sendToEmailTeam
     * @param bool   $sendEmailToBillingInfo
     */
    public function __construct(
        Event $event,
        $name,
        $subject,
        $emailTemplate,
        $sendToEmailTeam = false,
        $sendEmailToBillingInfo = false
    ) {
        $this->event                  = $event;
        $this->subject                = $subject;
        $this->emailTemplate          = $emailTemplate;
        $this->name                   = $name;
        $this->sendToEmailTeam        = $sendToEmailTeam;
        $this->sendEmailToBillingInfo = $sendEmailToBillingInfo;
    }
}
