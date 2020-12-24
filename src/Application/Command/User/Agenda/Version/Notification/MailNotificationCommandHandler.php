<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Components\Mail\AbstractMail;
use Proximum\Vimeet\Application\Components\Transactional\Mail\PrepareHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareVersionDiffChangedMailView;

class MailNotificationCommandHandler
{
    /** @var PrepareHandler */
    private $prepareHandler;

    /** @var MailerInterface */
    private $mailer;

    public function __construct(
        PrepareHandler $prepareHandler,
        MailerInterface $mailer
    ) {
        $this->prepareHandler = $prepareHandler;
        $this->mailer = $mailer;
    }

    public function handle(MailNotificationCommand $command): void
    {
        $mail = $this->prepareHandler->handle(new PrepareVersionDiffChangedMailView(
            $command->event,
            $command->user,
            $command->sheet,
            $command->user->getLocale(),
            $command->agendaModifications
        ));

        if (!$mail instanceof AbstractMail) {
            return;
        }

        $this->mailer->send($mail);
    }
}
