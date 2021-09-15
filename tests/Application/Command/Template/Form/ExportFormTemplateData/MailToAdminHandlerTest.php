<?php

namespace Proximum\Vimeet\Tests\Application\Command\Template\Form\ExportFormTemplateData;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Command\Template\Form\ExportFormTemplateData\MailToAdmin;
use Proximum\Vimeet\Application\Command\Template\Form\ExportFormTemplateData\MailToAdminHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Template\Form\Export\ExportFormTemplateDataMail;

class MailToAdminHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $admin = $this->prophesize(Admin::class);
        $event = $this->prophesize(Event::class);
        $file = $this->prophesize(File::class);

        $admin->getEmail()->shouldBeCalled()->willReturn('admin@receiver.net');

        $mail = new ExportFormTemplateDataMail('sender@example.net', 'admin@receiver.net', 'fr', $event->reveal(), $file->reveal());

        $mailer = $this->prophesize(MailerInterface::class);
        $mailer->send($mail)->shouldBeCalled();

        $handler = new MailToAdminHandler(
            $mailer->reveal(),
            'sender@example.net'
        );

        $handler->handle(new MailToAdmin($admin->reveal(), $file->reveal(), $event->reveal(), 'fr'));
    }
}
