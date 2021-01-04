<?php

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Export;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Command\Rooming\Export\MailToAdmin;
use Proximum\Vimeet\Application\Command\Rooming\Export\MailToAdminHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Rooming\ExportList\ExportRoomingListMail;

class MailToAdminHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $file = $this->prophesize(File::class);
        $admin = $this->prophesize(Admin::class);

        $admin->getEmail()->shouldBeCalled()->willReturn('receiver@example.net');

        $mailer = $this->prophesize(MailerInterface::class);

        $mailer->send(new ExportRoomingListMail(
            'sender@exampl.net',
            'receiver@example.net',
            'fr',
            $event->reveal(),
            $file->reveal()
        ));

        $handler = new MailToAdminHandler(
            $mailer->reveal(),
            'sender@example.net'
        );

        $handler->handle(new MailToAdmin($event->reveal(), $admin->reveal(), $file->reveal(), 'fr'));
    }
}
