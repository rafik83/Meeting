<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Command;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;

class PrintPdfMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.sheet.print.pdf.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail/Sheet:printPdf.html.twig';

    /** @var string */
    protected $messageId = 'sheets_pdf';

    /** @var Event */
    public $event;

    /** @var string */
    public $fileHash;

    /** @var int */
    public $fileId;

    /**
     * @param Event    $event
     * @param string   $sender
     * @param string   $receiver
     * @param string   $locale
     * @param string   $fileHash
     * @param int|null $fileId
     */
    public function __construct(
        Event $event,
        string $sender,
        string $receiver,
        string $locale,
        string $fileHash,
        int $fileId = null
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event    = $event;
        $this->fileHash = $fileHash;
        $this->fileId   = $fileId;
    }
}
