<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class SpotManager
{
    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SpotRepositoryInterface $spotRepository, SheetRepositoryInterface $sheetRepository)
    {
        $this->spotRepository  = $spotRepository;
        $this->sheetRepository = $sheetRepository;
    }

    public function create(
        Event $event,
        string $reference,
        int $meetingCapacity,
        int $seatCapacity,
        bool $active,
        bool $isVisio = false
    ): Spot {
        $spot = new Spot(
            $reference,
            $event,
            1,
            $meetingCapacity,
            $seatCapacity,
            $active,
            Spot::PRIORITY_MUTUALIZE,
            $isVisio
        );
        $this->spotRepository->add($spot);

        return $spot;
    }

    public function getByReference(Event $event, string $reference): Spot
    {
        return $this->spotRepository->findByReference($event, $reference);
    }

    public function assignToSheet(Event $event, Sheet $sheet, string $reference): void
    {
        $spot = $this->spotRepository->findByReference($event, $reference);

        if (null === $spot) {
            throw new \InvalidArgumentException('Given spot reference not exists');
        }

        $sheet->setSpot($spot);
        $this->sheetRepository->set($sheet);
    }
}
