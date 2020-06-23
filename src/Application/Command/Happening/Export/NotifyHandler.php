<?php

namespace Proximum\Vimeet\Application\Command\Happening\Export;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\OMZ\NotifyOMZExportMail;

class NotifyHandler
{
    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $sender;

    public function __construct(MailerInterface $mailer, string $sender)
    {
        $this->mailer = $mailer;
        $this->sender = $sender;
    }

    public function handle(Notify $command): void
    {
        // @todo: change the mail and add a download file controller
        $mail = new NotifyOMZExportMail(
            $command->event,
            $this->sender,
            $command->admin->getEmail(),
            $command->locale,
            $command->file->getHash(),
            $command->file->getId()
        );

        $this->mailer->send($mail);
    }
}
