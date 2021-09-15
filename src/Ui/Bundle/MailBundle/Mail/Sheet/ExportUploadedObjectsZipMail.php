<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class ExportUploadedObjectsZipMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.event.export.uploaded_objects_zip.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Event/exportedUploadedObjectsZip.html.twig';

    /** @var Event */
    public $event;

    /** @var File */
    public $file;

    public function __construct(
        Event $event,
        File $file,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->file = $file;
    }

    public function getSubjectParameters(): array
    {
        return [
            '%event%' => $this->event->getTitle(),
        ];
    }
}
