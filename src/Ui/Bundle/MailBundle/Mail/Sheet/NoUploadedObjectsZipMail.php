<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;

class NoUploadedObjectsZipMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.event.export.no_uploaded_objects.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Event/noUploadedObjectsZip.html.twig';

    /** @var Event */
    public $event;

    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
    }

    public function getSubjectParameters(): array
    {
        return [
            '%event%' => $this->event->getTitle(),
        ];
    }
}
