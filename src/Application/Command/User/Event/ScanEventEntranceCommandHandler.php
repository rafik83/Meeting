<?php

namespace Proximum\Vimeet\Application\Command\User\Event;

use Proximum\Vimeet\Domain\Model\User\Event\Scan;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\Type;

class ScanEventEntranceCommandHandler
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

    public function handle(ScanEventEntranceCommand $command): void
    {
        $this->scanRepository->add(
            new Scan(
                $command->event,
                $command->user,
                $command->scannedAt,
                $this->dateTime,
                Type::TYPE_EVENT_ENTRANCE
            )
        );
    }
}
