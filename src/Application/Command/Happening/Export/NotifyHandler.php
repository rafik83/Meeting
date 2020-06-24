<?php

namespace Proximum\Vimeet\Application\Command\Happening\Export;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\Notification\NotifyAdmin;

class NotifyHandler
{
    /** @var MailerInterface */
    private $mailer;

    /** @var RouterInterface */
    private $router;

    /** @var TranslatorInterface */
    private $translator;

    /** @var string */
    private $sender;

    public function __construct(
        MailerInterface $mailer,
        RouterInterface $router,
        TranslatorInterface $translator,
        string $sender
    ) {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->translator = $translator;
        $this->sender = $sender;
    }

    public function handle(Notify $command): void
    {
        $mail = new NotifyAdmin(
            $this->translator->trans(
                'admin.mail.export_happening_participants.subject',
                ['%event%' => $command->event->getTitle()],
                'mail',
                $command->locale
            ),
            $this->sender,
            $command->admin->getEmail(),
            $command->locale,
            $this->translator->trans('admin.mail.export_happening_participants.content', [], 'mail', $command->locale),
            $this->router->generate(
                'admin_file_download',
                [
                    'event' => $command->event->getId(),
                    'hash' => $command->file->getHash(),
                    'file' => $command->file->getId()
                ]
            ),
            $this->translator->trans(
                'admin.mail.export_happening_participants.linkTitle',
                [],
                'mail',
                $command->locale
            ),
            null,
            $command->admin
        );

        $this->mailer->send($mail);
    }
}
