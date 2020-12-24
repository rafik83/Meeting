<?php

namespace Proximum\Vimeet\Tests\Application\Command\OMZ;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Command\OMZ\Notify;
use Proximum\Vimeet\Application\Command\OMZ\NotifyHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\OMZ\NotifyOMZExportMail;

class NotifyHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);
        $file = $this->prophesize(File::class);
        $admin->getEmail()->willReturn('admin@example.net');
        $admin->getLocale()->willReturn('fr');
        $file->getId()->willReturn(12);
        $file->getHash()->willReturn('hash');

        $sender = 'dev@example.net';
        $mailer = $this->prophesize(MailerInterface::class);
        $mail = new NotifyOMZExportMail(
            $event->reveal(),
            $sender,
            'admin@example.net',
            'fr',
            'hash',
            12
        );
        $mailer->send($mail)->shouldBeCalled();

        $command = new Notify($event->reveal(), $admin->reveal(), $file->reveal());
        $handler = new NotifyHandler($mailer->reveal(), $sender);

        $handler->handle($command);
    }
}
