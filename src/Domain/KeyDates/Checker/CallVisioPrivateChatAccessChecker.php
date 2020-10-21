<?php


namespace Proximum\Vimeet\Domain\KeyDates\Checker;


use Proximum\Vimeet\Domain\Model\ChatSession;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\ChatSessionRepository;

class CallVisioPrivateChatAccessChecker extends AccessChecker
{
    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    public function __construct(
        ChatSessionRepositoryInterface $chatSessionRepository,
        \DateTimeInterface $dateTime
    )
    {
        parent::__construct($dateTime);
        $this->chatSessionRepository = $chatSessionRepository;
    }
    public function allowedToAccess(Event $event, ChatSession $chatSession) : bool
    {
        if (null === $event->getConfiguration()->getCallVisioOpenDate() && null === $event->getConfiguration()->getCallVisioCloseDate()) {
            return false;
        }

        if ($this->datetime <= $event->getConfiguration()->getCallVisioOpenDate() || $this->datetime >= $event->getConfiguration()->getCallVisioCloseDate()) {
            return false;
        }

        return $this->chatSessionRepository->hasMessageFromUser($chatSession, $chatSession->getFromUser())
            || $this->chatSessionRepository->hasMessageFromUser($chatSession, $chatSession->getToUser());
    }
}
