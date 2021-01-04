<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Meeting;

use Proximum\Vimeet\Application\Command\User\Event\MeetingPlanningModifiedCommand;
use Proximum\Vimeet\Application\Command\User\Event\MeetingPlanningModifiedCommandHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingCreatedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingMovedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingMovedSpotEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlanningModifiedEventSubscriber implements EventSubscriberInterface
{
    /** @var MeetingPlanningModifiedCommandHandler */
    private $meetingPlanningModifiedCommandHandler;

    public function __construct(MeetingPlanningModifiedCommandHandler $meetingPlanningModifiedCommandHandler)
    {
        $this->meetingPlanningModifiedCommandHandler = $meetingPlanningModifiedCommandHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::MEETING_CREATED => 'onMeetingCreated',
            Events::MEETING_MOVED => 'onMeetingMoved',
            Events::MEETING_MOVED_SPOT => 'onMeetingMovedSpot',
            Events::MEETING_REMOVED => 'onMeetingRemoved',
        ];
    }

    private function handleParticipants(array $participants = []): void
    {
        foreach ($participants as $participant) {
            $this->meetingPlanningModifiedCommandHandler->handle(new MeetingPlanningModifiedCommand(
                $participant->getEvent(),
                $participant->getUser()
            ));
        }
    }

    public function onMeetingCreated(MeetingCreatedEvent $event): void
    {
        if ($event->getMeeting()->isCreatedByParticipants()) {
            return;
        }

        $this->handleParticipants($event->getMeeting()->getAllParticipants());
    }

    public function onMeetingMoved(MeetingMovedEvent $event): void
    {
        $this->handleParticipants($event->getMeeting()->getAllParticipants());
    }

    public function onMeetingMovedSpot(MeetingMovedSpotEvent $event): void
    {
        $this->handleParticipants($event->getMeeting()->getAllParticipants());
    }

    public function onMeetingRemoved(MeetingRemovedEvent $event): void
    {
        $this->handleParticipants($event->getParticipants());
    }
}
