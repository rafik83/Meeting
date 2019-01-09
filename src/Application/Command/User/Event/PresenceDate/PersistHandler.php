<?php

namespace Proximum\Vimeet\Application\Command\User\Event\PresenceDate;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\User\Event\PresenceDate;
use Proximum\Vimeet\Domain\Repository\User\Event\PresenceDateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\DateTime as DateTimeObject;

class PersistHandler
{
    /** @var PresenceDateRepositoryInterface */
    private $presenceDateRepository;

    public function __construct(PresenceDateRepositoryInterface $presenceDateRepository)
    {
        $this->presenceDateRepository = $presenceDateRepository;
    }

    public function handle(Persist $persist): void
    {
        $presenceDate = $this->presenceDateRepository->getByUserAndEvent($persist->user, $persist->event);

        $departureObject = $persist->block->getObjectByTag(Tag::PARTICIPANT_DEPARTURE_DATE);
        $arrivalObject = $persist->block->getObjectByTag(Tag::PARTICIPANT_ARRIVAL_DATE);

        $departure = $departureObject instanceof DateTimeObject ? $departureObject->getDatetime() : null;
        $arrival = $arrivalObject instanceof DateTimeObject ? $arrivalObject->getDatetime() : null;

        if ($departureObject instanceof DateTimeObject
            && $arrivalObject instanceof DateTimeObject
            && $presenceDate instanceof PresenceDate
        ) {
            $this->presenceDateRepository->remove($presenceDate);
        }

        if (!$departure instanceof \DateTimeInterface
            && !$arrival instanceof \DateTimeInterface
        ) {
            return;
        }

        $this->presenceDateRepository->add(new PresenceDate(
            $persist->user,
            $persist->event,
            $arrival,
            $departure,
            $arrival  instanceof \DateTimeInterface ? $arrivalObject->displayHours() : false,
            $departure  instanceof \DateTimeInterface ? $departureObject->displayHours() : false
        ));
    }
}
