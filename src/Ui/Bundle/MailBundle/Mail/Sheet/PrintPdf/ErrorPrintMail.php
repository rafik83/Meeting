<?php

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet\PrintPdf;

use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Domain\Model\Event;

class ErrorPrintMail extends AbstractMail
{
    /** @var string */
    protected $subject = 'mail.sheet.print.pdf.error.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail/Sheet:printPdfError.html.twig';

    /** @var string */
    protected $messageId = 'sheets_pdf_error';

    /** @var Event */
    public $event;

    /**
     * @param Event  $event
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     */
    public function __construct(
        Event $event,
        string $sender,
        string $receiver,
        string $locale
    ) {
        parent::__construct($sender, $receiver, $locale);

        $this->event = $event;
    }
}
