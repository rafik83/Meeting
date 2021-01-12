<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Exception\Meeting\UpdateMeetingException;
use Proximum\Vimeet\Domain\Meeting\CanMoveMeeting;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateHandler
{
    private CommandBusInterface $commandBus;
    private CanMoveMeeting $canMoveMeeting;
    private TranslatorInterface $translator;
    private MessageRepositoryInterface $messageRepository;
    private MeetingRepositoryInterface $meetingRepository;
    private DateTimeInterface $datetime;
    private RequestRepositoryInterface $requestRepository;

    public function __construct(
        CanMoveMeeting $canMoveMeeting,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        MessageRepositoryInterface $messageRepository,
        MeetingRepositoryInterface $meetingRepository,
        DateTimeInterface $datetime,
        RequestRepositoryInterface $requestRepository
    ) {
        $this->canMoveMeeting = $canMoveMeeting;
        $this->commandBus = $commandBus;
        $this->translator = $translator;
        $this->messageRepository = $messageRepository;
        $this->meetingRepository = $meetingRepository;
        $this->datetime = $datetime;
        $this->requestRepository = $requestRepository;
    }

    public function handle(Update $command): void
    {
        if (false === $this->canMoveMeeting->isSatisfiedBy($command->sheet)
            || !$command->meeting->hasSheet($command->sheet)
        ) {
            throw new AccessDeniedException();
        }

        $command->meeting->setParticipants($command->sheet, $command->participants);

        try {

            $this->commandBus->handle(
                new UpdateSlot(
                    $command->meeting,
                    $command->meetingSlot,
                    $command->meeting->isVisio(),
                    true
                )
            );

            $command->meeting->blockSlot();

            $request = $command->meeting->getRequest();

            if ($command->content) {
                $message = new Message(
                    $request,
                    $command->sheet,
                    $command->content,
                    $this->datetime
                );

                $request->setUpdateOrDeleteReasonMessage($message);
                $this->messageRepository->add($message);
            } else {
                $request->setUpdateOrDeleteReasonMessage(null);
            }

            $this->meetingRepository->set($command->meeting);
            $this->requestRepository->set($request);
        } catch (\Exception $exception) {
            throw new UpdateMeetingException(
                $this->translator->trans('agenda.meeting.updateSlot.error')
            );
        }
    }
}
