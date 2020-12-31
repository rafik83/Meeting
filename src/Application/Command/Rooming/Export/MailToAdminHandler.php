<?php

namespace Proximum\Vimeet\Application\Command\Rooming\Export;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Rooming\ExportList\ExportRoomingListMail;

class MailToAdminHandler
{
    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $sender;

    public function __construct(
        MailerInterface $mailer,
        string $sender
    ) {
        $this->mailer = $mailer;
        $this->sender = $sender;
    }

    public function handle(MailToAdmin $command): void
    {
        $mail = new ExportRoomingListMail(
            $this->sender,
            $command->admin->getEmail(),
            $command->locale,
            $command->event,
            $command->file
        );

        $this->mailer->send($mail);
    }
}
