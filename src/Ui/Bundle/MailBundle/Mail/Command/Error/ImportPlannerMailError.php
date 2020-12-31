<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command\Error;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;

class ImportPlannerMailError extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.planner.import.subject.error';

    /** @var string */
    protected $template = 'MailBundle:Mail:Planner/error.html.twig';

    /** @var string */
    protected $messageId = 'import_planner_error';

    /** @var int */
    public $eventId;

    /** @var string */
    public $message;

    /**
     * @param int    $eventId
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     * @param string $message
     */
    public function __construct(
        $eventId,
        $sender,
        $receiver,
        $locale,
        $message
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->eventId   = $eventId;
        $this->message = $message;
    }
}
