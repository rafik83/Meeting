<?php

namespace Proximum\Vimeet\Application\Command\Event\ExtraData;

use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;

class AddOrUpdateHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(ExtraDataRepositoryInterface $extraDataRepository, \DateTimeInterface $dateTime)
    {
        $this->extraDataRepository = $extraDataRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(AddOrUpdate $addOrUpdate): ExtraData
    {
        $eventExtraData = $this->extraDataRepository->getExtraDataForEvent($addOrUpdate->event, $addOrUpdate->name);

        if ($eventExtraData instanceof ExtraData) {
            $eventExtraData->update($addOrUpdate->value, $this->dateTime);
            $this->extraDataRepository->set($eventExtraData);
        } else {
            $eventExtraData = new ExtraData(
                $addOrUpdate->event,
                $addOrUpdate->name,
                $addOrUpdate->value,
                $this->dateTime
            );
            $this->extraDataRepository->add($eventExtraData);
        }

        return $eventExtraData;
    }
}
