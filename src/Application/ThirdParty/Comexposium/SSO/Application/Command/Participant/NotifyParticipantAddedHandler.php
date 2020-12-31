<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Application\Command\Participant;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQuery;
use Proximum\Vimeet\Application\Query\Mail\ParticipantMailViewQueryHandler;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\ThirdParty\Comexposium\SSO\Participant\ParticipantAddedMail;

class NotifyParticipantAddedHandler
{
    /** @var MailerInterface */
    private $mailer;

    /** @var EventSender */
    private $sender;

    /** @var ParticipantMailViewQueryHandler */
    private $participantMailViewQueryHandler;

    public function __construct(
        MailerInterface $mailer,
        ParticipantMailViewQueryHandler $participantMailViewQueryHandler,
        EventSender $sender
    ) {
        $this->mailer = $mailer;
        $this->sender = $sender;
        $this->participantMailViewQueryHandler = $participantMailViewQueryHandler;
    }

    public function handle(NotifyParticipantAdded $command): void
    {
        $participantInfoView = $this->participantMailViewQueryHandler->handle(
            new ParticipantMailViewQuery(
                $command->participant->getSheet(),
                $command->participant->getUser()
            )
        );

        $this->mailer->send(new ParticipantAddedMail(
            $command->event,
            $this->sender->generate($command->event),
            $command->participant->getEmail(),
            $command->locale,
            $participantInfoView
        ));
    }
}
