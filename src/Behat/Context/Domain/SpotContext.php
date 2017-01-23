<?php

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Service\Storage;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class SpotContext implements Context
{
    /** @var Storage */
    private $storage;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param Storage                  $storage
     * @param SpotRepositoryInterface  $spotRepository
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(
        Storage $storage,
        SpotRepositoryInterface $spotRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->storage = $storage;
        $this->spotRepository = $spotRepository;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @Given /^there is an active spot "(?P<reference>[^"]+)" with meeting capacity of (?P<meetingCapacity>\d+), seat capacity of (?P<seatCapacity>\d+)$/
     *
     * @param string $reference
     * @param int    $meetingCapacity
     * @param int    $seatCapacity
     *
     * @return Spot
     */
    public function createSpot($reference, $meetingCapacity, $seatCapacity)
    {
        if (!$this->storage->getLastEvent()) {
            throw new \InvalidArgumentException('Missing event');
        }

        $spot = new Spot($reference, $this->storage->getLastEvent(), 1, $meetingCapacity, $seatCapacity, true);
        $this->spotRepository->add($spot);

        return $spot;
    }

    /**
     * @Given /^spot "(?P<spotReference>[^"]+)" is assigned to this sheet$/
     *
     * @param string $spotReference
     */
    public function spotIsAssignedToAnotherSheet($spotReference)
    {
        if (!$this->storage->getLastEvent()) {
            throw new \InvalidArgumentException('Missing event');
        }

        if (!$this->storage->getLastSheet()) {
            throw new \InvalidArgumentException('Missing sheet');
        }

        $spot = $this->getSpotRepository()->findByReference($this->storage->getLastEvent(), $spotReference);

        if (null === $spot) {
            throw new \InvalidArgumentException('Given spot reference not exists');
        }

        $this->storage->getLastSheet()->setSpot($spot);
        $this->sheetRepository->set($this->storage->getLastSheet());
    }
}
