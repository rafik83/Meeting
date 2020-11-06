<?php


namespace Proximum\Vimeet\Application\Components\Contact;

use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ChatSessionRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\CanScanParticipant;

class CanAccessToContacts
{
    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    /** @var CanScanParticipant */
    private $canScanParticipant;

    /** @var ChatSessionRepositoryInterface */
    private $chatSessionRepository;

    public function __construct(
        EventOpenAccessChecker $eventOpenAccessChecker,
        CanScanParticipant $canScanParticipant,
        ChatSessionRepositoryInterface $chatSessionRepository
    ){
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->canScanParticipant = $canScanParticipant;
        $this->chatSessionRepository = $chatSessionRepository;
    }

    public function isSatisfiedBy(Event $event, User $user, Sheet $sheet): bool {
        if (!$this->eventOpenAccessChecker->allowedToAccess($event)) {
            return false;
        }

        if ($this->chatSessionRepository->hasAStartedVisio($event, $user)) {
            return true;
        }

        if ($this->canScanParticipant->isSatisfiedBy($sheet)) {
            return true;
        }

        if ($sheet->isInInternalCatalog()) {
            return true;
        }

        return false;
    }
}
