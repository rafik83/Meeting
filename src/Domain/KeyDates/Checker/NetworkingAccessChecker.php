<?php


namespace Proximum\Vimeet\Domain\KeyDates\Checker;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Networking\Sheet\CanAccessToNetworking;

class NetworkingAccessChecker extends AccessChecker
{
    private CanAccessToNetworking $canAccessToNetworking;

    public function __construct(DateTimeInterface $dateTime, CanAccessToNetworking $canAccessToNetworking) {
        parent::__construct($dateTime);
        $this->canAccessToNetworking = $canAccessToNetworking;
    }

    public function isEnabled(Event $event): bool
    {
        if (null === $event->getConfiguration()->getNetworkingOpenDate() && null === $event->getConfiguration()->getNetworkingCloseDate()) {
            return false;
        }

        return $this->dateTime <= $event->getConfiguration()->getNetworkingCloseDate() && $this->dateTime >= $event->getConfiguration()->getNetworkingOpenDate();
    }

    public function isSheetAllowedToAccess(Sheet $sheet) {
        if (!$this->canAccessToNetworking->isSatisfiedBy($sheet)) {
            return false;
        }

        return $this->isEnabled($sheet->getEvent());
    }
}
