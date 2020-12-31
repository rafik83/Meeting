<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;

class ImportPlannerMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.planner.import.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Planner/import.html.twig';

    /** @var string */
    protected $messageId = 'import_planner';

    /** @var Event */
    public $event;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     */
    public function __construct(Event $event, $sender, $receiver, $locale)
    {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
    }
}
