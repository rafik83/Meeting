<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use DateTimeInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\MeetingRequest\RefusedRequestEvent;
use Proximum\Vimeet\Domain\Model\Meeting\Message;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class RefuseRequestHandler
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepository;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * RefuseRequestHandler constructor.
     *
     * @param RequestRepositoryInterface $requestRepository
     * @param MessageRepositoryInterface $messageRepository
     * @param DelayedEventDispatcher     $eventDispatcher
     * @param DateTimeInterface          $createdAt
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        MessageRepositoryInterface $messageRepository,
        DelayedEventDispatcher $eventDispatcher,
        DateTimeInterface $createdAt
    ) {
        $this->requestRepository = $requestRepository;
        $this->messageRepository = $messageRepository;
        $this->eventDispatcher   = $eventDispatcher;
        $this->createdAt         = $createdAt;
    }

    /**
     * @param RefuseRequest $refuseRequest
     */
    public function handle(RefuseRequest $refuseRequest)
    {
        // Add message
        if ($refuseRequest->message) {
            $this->messageRepository->add(new Message(
                $refuseRequest->request,
                $refuseRequest->request->getToSheet(),
                $refuseRequest->message,
                $this->createdAt
            ));
            $refuseRequest->request->setHasMessage(true);
        }

        // Refuse request
        $this->requestRepository->set($refuseRequest->request->refuse($this->createdAt));

        // Dispatch event
        $this->eventDispatcher->dispatch(
            Events::MEETING_REQUEST_REFUSED,
            new RefusedRequestEvent(
                $refuseRequest->emitter,
                $refuseRequest->request,
                $this->createdAt,
                $refuseRequest->message
            )
        );
    }
}
