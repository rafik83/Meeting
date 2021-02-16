<?php

namespace Proximum\Vimeet\Application\Command\Scan\Happening;

use Proximum\Vimeet\Domain\Model\User\Event\Scan;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\Type;

class ScanHappeningHandler
{
    /** @var ScanRepositoryInterface */
    private $scanRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ScanRepositoryInterface $scanRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->scanRepository = $scanRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(ScanHappening $command): void
    {
        $hasScan = $this->scanRepository->hasScanForUserEventTypeAndObjectId(
            $command->user,
            $command->event,
            Type::TYPE_HAPPENING_ENTRANCE,
            $command->happening->getId()
        );

        if (true === $hasScan) {
            return;
        }

        $scan = new Scan(
            $command->event,
            $command->user,
            $command->scannedAt,
            $this->dateTime,
            Type::TYPE_HAPPENING_ENTRANCE,
            $command->happening->getId()
        );

        $this->scanRepository->add($scan);
    }
}
