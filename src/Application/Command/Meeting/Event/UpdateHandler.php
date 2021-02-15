<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Exception\Meeting\UpdateMeetingException;
use Proximum\Vimeet\Domain\Meeting\CanUpdateMeeting;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateHandler
{
    private CommandBusInterface $commandBus;
    private CanUpdateMeeting $canUpdateMeeting;
    private TranslatorInterface $translator;
    private MessageRepositoryInterface $messageRepository;
    private MeetingRepositoryInterface $meetingRepository;
    private DateTimeInterface $dateTime;
    private RequestRepositoryInterface $requestRepository;
    private ?LoggerInterface $logger;

    public function __construct(
        CanUpdateMeeting $canUpdateMeeting,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        MessageRepositoryInterface $messageRepository,
        MeetingRepositoryInterface $meetingRepository,
        DateTimeInterface $dateTime,
        RequestRepositoryInterface $requestRepository,
        ?LoggerInterface $logger = null
    ) {
        $this->canUpdateMeeting = $canUpdateMeeting;
        $this->commandBus = $commandBus;
        $this->translator = $translator;
        $this->messageRepository = $messageRepository;
        $this->meetingRepository = $meetingRepository;
        $this->dateTime = $dateTime;
        $this->requestRepository = $requestRepository;
        $this->logger = $logger;
    }

    public function handle(Update $command): void
    {
        if (false === $this->canUpdateMeeting->isSatisfiedBy($command->sheet)
            || !$command->meeting->hasSheet($command->sheet)
        ) {
            throw new AccessDeniedException();
        }

        if (0 === \count($command->participants)) {
            throw new UpdateMeetingException('At least one participant of the sheet must be in the meeting');
        }

        try {
            $command->meeting->setParticipants($command->sheet, $command->participants);

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
                    $this->dateTime
                );

                $request->setUpdateOrDeleteReasonMessage($message);
                $this->messageRepository->add($message);
            } else {
                $request->setUpdateOrDeleteReasonMessage(null);
            }

            $this->meetingRepository->set($command->meeting);
            $this->requestRepository->set($request);
        } catch (\Exception $exception) {
            if ($this->logger) {
                $this->logger->warning(
                    'Update meeting {id} failed: [{exc}] {message}',
                    ['id' => $command->meeting->getId(), 'exc'=>get_class($exception), 'message' => $exception->getMessage()]
                );
            }

            throw new UpdateMeetingException(
                $this->translator->trans('agenda.meeting.updateSlot.error')
            );
        }
    }
}
