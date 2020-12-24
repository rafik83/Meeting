<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Happening;

use Proximum\Vimeet\Application\Adapter\MailerInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\HappeningParticipationAutomaticallyUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Happening\HappeningParticipationAutomaticallyUpdatedMail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HappeningParticipationEventSubscriber implements EventSubscriberInterface
{
    /** @var MailerInterface */
    private $mailer;

    /** @var string */
    private $sender;

    public function __construct(MailerInterface $mailer, string $sender)
    {
        $this->mailer = $mailer;
        $this->sender = $sender;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::HAPPENING_PARTICIPATION_AUTOMATICALLY_UPDATED => 'onParticipationAutomaticallyUpdated',
        ];
    }

    public function onParticipationAutomaticallyUpdated(HappeningParticipationAutomaticallyUpdatedEvent $event): void
    {
        $locale = $event->sheet
            ->getEvent()
            ->getAvailableLocale($event->sheet->getOwner()->getLocale());

        $email = new HappeningParticipationAutomaticallyUpdatedMail(
            $event->happeningParticipationViews,
            $event->sheet->getEvent(),
            $this->sender,
            $event->sheet->getOwner()->getEmail(),
            $locale
        );

        foreach ($event->happeningParticipationViews as $happeningParticipationView) {
            /** @var Participant $addedParticipant */
            foreach ($happeningParticipationView->addedParticipants as $addedParticipant) {
                $email->addReceiver($addedParticipant->getEmail());
            }

            /** @var Participant $removedParticipant */
            foreach ($happeningParticipationView->removedParticipants as $removedParticipant) {
                $email->addReceiver($removedParticipant->getEmail());
            }
        }

        $this->mailer->send($email);
    }
}
