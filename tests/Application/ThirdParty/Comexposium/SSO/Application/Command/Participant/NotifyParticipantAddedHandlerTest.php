<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant\NotifyParticipantAdded;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant\NotifyParticipantAddedHandler;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\ThirdParty\Comexposium\SSO\Participant\ParticipantAddedMail;

class NotifyParticipantAddedHandlerTest extends TestCase
{
    public function testHandle()
    {
        $participant = $this->prophesize(Participant::class);
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $sheet = $this->prophesize(Sheet::class);

        $participant->getSheet()->willReturn($sheet->reveal());
        $participant->getUser()->willReturn($user->reveal());
        $participant->getEmail()->willReturn('email@example.net');

        $mailer = $this->prophesize(MailerInterface::class);
        $participantMailViewQueryHandler = $this->prophesize(ParticipantMailViewQueryHandler::class);
        $sender = $this->prophesize(EventSender::class);

        $view = $this->prophesize(ParticipantInfoView::class);

        $participantMailViewQueryHandler->handle(new ParticipantMailViewQuery($sheet->reveal(), $user->reveal()))
            ->shouldBeCalled()
            ->willReturn($view->reveal())
        ;
        $sender->generate($event->reveal())->shouldBeCalled()->willReturn('sender@vimeet.events');

        $mailer
            ->send(
                new ParticipantAddedMail(
                    $event->reveal(),
                    'sender@vimeet.events',
                    'email@example.net',
                    'fr',
                    $view->reveal()
                )
            )
            ->shouldBeCalled()
        ;

        $handler = new NotifyParticipantAddedHandler(
            $mailer->reveal(),
            $participantMailViewQueryHandler->reveal(),
            $sender->reveal()
        );

        $handler->handle(new NotifyParticipantAdded($event->reveal(), $participant->reveal(), 'fr'));
    }
}
