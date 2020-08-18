<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening\Webinar;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;

class ZipRecordArchivePreparedMail extends AbstractMail
{
    public const SUBJECT = 'mail.happening.participation.subject';
    public const TEMPLATE = 'MailBundle:Mail:Happening/participation.html.twig';

    /** @var string */
    protected $subject = self::SUBJECT;

    /** @var string */
    protected $template = self::TEMPLATE;

    /** @var string */
    protected $messageId = Events::HAPPENING_ZIP_RECORD_ARCHIVE_PREPARED;

    /** @var Happening */
    public $happening;

    /** @var Event */
    public $event;

    public function __construct(
        Happening $happening,
        Event $event,
        $sender,
        $receiver,
        $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
        $this->happening = $happening;
    }
}
