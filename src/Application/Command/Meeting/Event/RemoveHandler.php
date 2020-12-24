<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Application\Exception\Meeting\RemoveMeetingException;
use Proximum\Vimeet\Domain\Meeting\CanRemoveMeeting;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RemoveHandler
{
    /**@var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    private $datetime;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var CanRemoveMeeting */
    private $canRemoveMeeting;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    public function __construct(
        MessageRepositoryInterface $messageRepository,
        MeetingRepositoryInterface $meetingRepository,
        DelayedEventDispatcherInterface $eventDispatcher,
        CanRemoveMeeting $canRemoveMeeting,
        \DateTimeInterface $datetime,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->meetingRepository  = $meetingRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime = $datetime;
        $this->messageRepository = $messageRepository;
        $this->canRemoveMeeting = $canRemoveMeeting;
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param Remove $command
     *
     * @throws RemoveMeetingException
     */
    public function handle(Remove $command)
    {
        if (false === $this->canRemoveMeeting->isSatisfiedBy($command->sheet)
            || !$command->meeting->hasSheet($command->sheet)
        ) {
            throw new AccessDeniedException();
        }

        try {
            $request = $command->meeting->getRequest();

            if ($command->content) {
                $message = new Message(
                    $request,
                    $command->sheet,
                    $command->content,
                    $this->datetime
                );
                $this->messageRepository->add($message);
                $request->setUpdateOrDeleteReasonMessage($message);
            } else {
                $request->setUpdateOrDeleteReasonMessage(null);
            }

            $this->meetingRepository->remove($command->meeting);
            $this->requestRepository->set($request);
        } catch (\Exception $exception){
            throw new RemoveMeetingException(
                'Can not remove meeting'
            );
        }

        $this->eventDispatcher->dispatch(
            Events::MEETING_REMOVED,
            new MeetingRemovedEvent(
                [
                    $command->meeting->getFromSheet(),
                    $command->meeting->getToSheet(),
                ],
                $command->meeting->getAllParticipants()
            )
        );
    }
}
