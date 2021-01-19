<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\Error;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;

class ExportPlannerMailError extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.planner.export.subject.error';

    /** @var string */
    protected $template = 'MailBundle:Mail:Planner/error.html.twig';

    /** @var string */
    protected $messageId = 'export_planner_error';

    /** @var Event */
    public $event;

    /** @var string */
    public $message;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param string $message
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        $message
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event   = $event;
        $this->message = $message;
    }
}
