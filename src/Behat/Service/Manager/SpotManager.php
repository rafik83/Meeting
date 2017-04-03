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

    /**
     * @param SpotRepositoryInterface  $spotRepository
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(SpotRepositoryInterface $spotRepository, SheetRepositoryInterface $sheetRepository)
    {
        $this->spotRepository  = $spotRepository;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param Event  $event
     * @param string $reference
     * @param int    $meetingCapacity
     * @param int    $seatCapacity
     *
     * @return Spot
     */
    public function create(Event $event, $reference, $meetingCapacity, $seatCapacity)
    {
        $spot = new Spot($reference, $event, 1, $meetingCapacity, $seatCapacity, true);
        $this->spotRepository->add($spot);

        return $spot;
    }

    /**
     * @param Event  $event
     * @param string $reference
     *
     * @return Spot|null
     */
    public function getByReference(Event $event, $reference)
    {
        return $this->spotRepository->findByReference($event, $reference);
    }

    /**
     * @param Event  $event
     * @param Sheet  $sheet
     * @param string $reference
     */
    public function assignToSheet(Event $event, Sheet $sheet, $reference)
    {
        $spot = $this->spotRepository->findByReference($event, $reference);

        if (null === $spot) {
            throw new \InvalidArgumentException('Given spot reference not exists');
        }

        $sheet->setSpot($spot);
        $this->sheetRepository->set($sheet);
    }
}
