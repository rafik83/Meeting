<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\Webservice\Spot;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\Notify;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Spot\NotifyHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Spot\NotifyExportMail;

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
        $mail = new NotifyExportMail(
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
