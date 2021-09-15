<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Meeting\MeetingRemovedEvent;
use Proximum\Vimeet\Application\Event\Meeting\MeetingUnParticipateEvent;
use Proximum\Vimeet\Application\Exception\Slot\LockedException;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class RemoveMeetingHandler
{
    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     * @param TranslatorInterface        $translator
     * @param DelayedEventDispatcher     $eventDispatcher
     * @param RequestRepositoryInterface $requestRepository
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        TranslatorInterface $translator,
        DelayedEventDispatcher $eventDispatcher,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->translator        = $translator;
        $this->eventDispatcher   = $eventDispatcher;
        $this->requestRepository = $requestRepository;
    }

    /**
     * @param RemoveMeeting $query
     *
     * @throws LockedException
     */
    public function handle(RemoveMeeting $query)
    {
        if ($query->meeting->isBlockedSlot()) {
            throw new LockedException($this->translator->trans(
                'flash.admin.meeting.remove.failed',
                [],
                'flashes',
                $query->user->getLocale()
            ));
        }

        $this->meetingRepository->remove($query->meeting);

        $request = $query->meeting->getRequest();

        $request->setUpdateOrDeleteReasonMessage(null);

        $this->requestRepository->set($request);

        $participants = $query->meeting->getAllParticipants();
        $this->eventDispatcher->dispatch(
            Events::MEETING_REMOVED,
            new MeetingRemovedEvent(
                [
                    $query->meeting->getFromSheet(),
                    $query->meeting->getToSheet(),
                ],
                $participants
            )
        );

        foreach ($participants as $participant) {
            $this->eventDispatcher->dispatch(
                Events::MEETING_UN_PARTICIPATE,
                new MeetingUnParticipateEvent($participant)
            );
        }
    }
}
