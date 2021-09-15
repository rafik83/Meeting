<?php

namespace Proximum\Vimeet\Domain\Unavailability\Mass;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;

class IsMassUnavailabilityAssignedToAllTypes
{
    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var int[] indexed by Event id  */
    private $cachedCountTypesByEvent = [];

    public function __construct(TypeRepositoryInterface $typeRepository)
    {
        $this->typeRepository = $typeRepository;
    }

    public function handle(Event $event, Mass $mass): bool
    {
        return $mass->countTypes() === $this->countTypesByEvent($event);
    }

    private function countTypesByEvent(Event $event): int
    {
        $eventId = $event->getId();

        if (!isset($this->cachedCountTypesByEvent[$eventId])) {
            $this->cachedCountTypesByEvent[$eventId] = $this->typeRepository->countByEvent($event);
        }

        return $this->cachedCountTypesByEvent[$eventId];
    }
}
