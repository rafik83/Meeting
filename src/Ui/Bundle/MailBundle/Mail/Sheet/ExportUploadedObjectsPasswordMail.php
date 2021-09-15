<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;

class ExportUploadedObjectsPasswordMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.event.export.uploaded_objects_password.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Event/exportedUploadedObjectsPassword.html.twig';

    /** @var Event */
    public $event;

    /** @var string */
    public $password;

    public function __construct(
        Event $event,
        string $password,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->password = $password;
    }

    public function getSubjectParameters(): array
    {
        return [
            '%event%' => $this->event->getTitle(),
        ];
    }
}
