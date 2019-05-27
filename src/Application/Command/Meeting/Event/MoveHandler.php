<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Event;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateSlot;
use Proximum\Vimeet\Application\Exception\Meeting\MoveMeetingException;
use Proximum\Vimeet\Domain\Meeting\CanMoveMeeting;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MoveHandler
{
    /** @var CommandBusInterface */
    private $commandBus;

    /** @var CanMoveMeeting */
    private $canMoveMeeting;

    /** @var TranslatorInterface */
    private $translator;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    /**@var RequestRepositoryInterface */
    private $requestRepository;

    public function __construct(
        CanMoveMeeting $canMoveMeeting,
        CommandBusInterface $commandBus,
        TranslatorInterface $translator,
        MessageRepositoryInterface $messageRepository,
        MeetingRepositoryInterface $meetingRepository,
        \DateTimeInterface $datetime,
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

    public function handle(Move $move): void
    {
        if (false === $this->canMoveMeeting->isSatisfiedBy($move->sheet)
            || !$move->meeting->hasSheet($move->sheet)
        ) {
            throw new AccessDeniedException();
        }

        try {
            $this->commandBus->handle(
                new UpdateSlot(
                    $move->meeting,
                    $move->meetingSlot,
                    $move->meeting->isVisio(),
                    true
                )
            );

            $move->meeting->blockSlot();

            if ($move->content) {
                $message = new Message(
                    $move->meeting->getRequest(),
                    $move->sheet,
                    $move->content,
                    $this->datetime
                );

                $move->meeting->getRequest()->setUpdateOrDeleteReasonMessage($message);
                $this->messageRepository->add($message);
            } else {
                $move->meeting->getRequest()->setUpdateOrDeleteReasonMessage(null);
            }

            $this->meetingRepository->set($move->meeting);
            $this->requestRepository->set($move->meeting->getRequest());
        } catch (\Exception $exception) {
            throw new MoveMeetingException(
                $this->translator->trans('agenda.meeting.updateSlot.error')
            );
        }
    }
}
