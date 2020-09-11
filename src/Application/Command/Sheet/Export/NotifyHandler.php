<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Export;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\Notification\NotifyAdmin;

class NotifyHandler
{
    /** @var MailerInterface */
    private $mailer;

    /** @var TranslatorInterface */
    private $translator;

    /** @var RouterInterface */
    private $router;

    /** @var string */
    private $sender;

    public function __construct(
        MailerInterface $mailer,
        TranslatorInterface $translator,
        RouterInterface $router,
        string $sender
    ) {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->router = $router;
        $this->sender = $sender;
    }

    public function handle(Notify $command): void
    {
        $mail = new NotifyAdmin(
            $this->translator->trans(
                'admin.mail.export_sheets.subject',
                ['%event%' => $command->event->getTitle()],
                'mail',
                $command->locale
            ),
            $this->sender,
            $command->admin->getEmail(),
            $command->locale,
            $this->translator->trans('admin.mail.export_sheets.content', [], 'mail', $command->locale),
            $this->router->generate(
                'admin_file_download',
                [
                    'event' => $command->event->getId(),
                    'hash' => $command->file->getHash(),
                    'file' => $command->file->getId()
                ]
            ),
            $this->translator->trans(
                'admin.mail.export_sheets.linkTitle',
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
