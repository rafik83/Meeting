<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet\Export;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Sheet\Export\Notify;
use Proximum\Vimeet\Application\Command\Sheet\Export\NotifyHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Admin\Notification\NotifyAdmin;

class NotifyHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);
        $file = $this->prophesize(File::class);
        $mailer = $this->prophesize(MailerInterface::class);
        $translator = $this->prophesize(TranslatorInterface::class);
        $router = $this->prophesize(RouterInterface::class);

        $admin->getEmail()->willReturn('admin@example.net');
        $event->getTitle()->willReturn('titre de l\'événement');
        $event->getId()->willReturn(12);

        $file->getHash()->willReturn('hash');
        $file->getId()->willReturn(10);

        $translator->trans(
            'admin.mail.export_sheets.subject',
            ['%event%' => 'titre de l\'événement'],
            'mail',
            'fr'
        )->shouldBeCalled()->willReturn('sujet');

        $translator->trans('admin.mail.export_sheets.content', [], 'mail', 'fr')
            ->shouldBeCalled()
            ->willReturn('contenu du mail')
        ;
        $translator->trans('admin.mail.export_sheets.linkTitle', [], 'mail', 'fr')
            ->shouldBeCalled()
            ->willReturn('lien')
        ;

        $router->generateAbsoluteUrl(
                'admin_file_download',
                [
                    'event' => 12,
                    'hash' => 'hash',
                    'file' => 10
                ]
            )
            ->shouldBeCalled()
            ->willReturn('https://link-to-file.events')
        ;

        $mail = new NotifyAdmin(
            'sujet',
            'proximum@example.net',
            'admin@example.net',
            'fr',
            'contenu du mail',
            'https://link-to-file.events',
            'lien',
            null,
            $admin->reveal()
        );
        $mailer->send($mail);

        $command = new Notify(
            $event->reveal(),
            $admin->reveal(),
            'fr',
            $file->reveal()
        );
        $handler = new NotifyHandler(
            $mailer->reveal(),
            $translator->reveal(),
            $router->reveal(),
            'proximum@example.net'
        );

        $handler->handle($command);
    }
}
