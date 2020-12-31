<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Event;

use Proximum\Vimeet\Application\Components\Mail\AbstractCustomizedMail;
use Proximum\Vimeet\Domain\Model\Event;

class PreRegisteredCustomizedMail extends AbstractCustomizedMail
{
    public function __construct(
        Event $event,
        string $sender,
        string $receiver,
        string $locale,
        string $subject,
        string $content
    ) {
        parent::__construct($event, $sender, $receiver, $locale);

        $this->subject = $subject;
        $this->content = $content;
        $this->sendToEmailTeam = true;
    }
}
