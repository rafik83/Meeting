<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Agenda\Version\Notification;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\MailNotificationCommand;
use Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification\MailNotificationCommandHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\PrepareHandler;
use Proximum\Vimeet\Application\Components\Transactional\Mail\View\PrepareVersionDiffChangedMailView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Agenda\VersionDiffChangedCustomizedMail;

class MailNotificationCommandHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $prepareHandler, $mailer, $sheet, $user, $event;

    public function setUp()
    {
        $this->prepareHandler = $this->prophesize(PrepareHandler::class);
        $this->mailer = $this->prophesize(MailerInterface::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet->getUserLocale($this->user->reveal())->willReturn('fr');
    }

    public function testHandleWithoutMail(): void
    {
        $this->prepareHandler
            ->handle(new PrepareVersionDiffChangedMailView(
                $this->event->reveal(),
                $this->user->reveal(),
                $this->sheet->reveal(),
                'fr',
                'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.'
            ))->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->mailer->send(Argument::any())->shouldNotBeCalled();
        $handler = new MailNotificationCommandHandler(
            $this->prepareHandler->reveal(),
            $this->mailer->reveal()
        );

        $command = new MailNotificationCommand(
            $this->event->reveal(),
            $this->user->reveal(),
            $this->sheet->reveal(),
            'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.'
        );
        $handler->handle($command);
    }

    public function testHandle(): void
    {
        $mail = new VersionDiffChangedCustomizedMail(
            $this->event->reveal(),
            'sender@example.net',
            'receiver@example.net',
            'fr',
            'subject',
            'content'
        );
        $this->prepareHandler
            ->handle(new PrepareVersionDiffChangedMailView(
                $this->event->reveal(),
                $this->user->reveal(),
                $this->sheet->reveal(),
                'fr',
                'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.'
            ))->shouldBeCalled()
            ->willReturn($mail)
        ;

        $this->mailer->send($mail)->shouldBeCalled();

        $handler = new MailNotificationCommandHandler(
            $this->prepareHandler->reveal(),
            $this->mailer->reveal()
        );

        $command = new MailNotificationCommand(
            $this->event->reveal(),
            $this->user->reveal(),
            $this->sheet->reveal(),
            'Votre rendez-vous avec Tata est déplacé à 10h00 en STAND10.'
        );
        $handler->handle($command);
    }
}
