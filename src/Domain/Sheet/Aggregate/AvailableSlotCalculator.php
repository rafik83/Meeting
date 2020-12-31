<?php

namespace Proximum\Vimeet\Domain\Sheet\Aggregate;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AvailableSlotCalculator implements AvailableSlotCalculatorInterface
{
    /** @var MeetingSlotRepositoryInterface */
    private $slotRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    /**
     * @param MeetingSlotRepositoryInterface $slotRepository
     * @param SheetRepositoryInterface       $sheetRepository
     * @param SheetIndexerInterface          $sheetIndexer
     */
    public function __construct(
        MeetingSlotRepositoryInterface $slotRepository,
        SheetRepositoryInterface $sheetRepository,
        SheetIndexerInterface $sheetIndexer
    ) {
        $this->slotRepository = $slotRepository;
        $this->sheetRepository = $sheetRepository;
        $this->sheetIndexer = $sheetIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public function calculateAvailableSlotForSheet(Sheet $sheet, bool $indexSheet = true): void
    {
        $slots = [];

        foreach ($sheet->getParticipants()->toArray() as $participant) {
            $slotsOfParticipant = $this->slotRepository->findAvailableSlotsByParticipants(
                $sheet->getEvent(),
                [$participant]
            );

            foreach ($slotsOfParticipant as $slot) {
                $slots[$slot->getId()] = $slot;
            }
        }

        $availableSlots = [];

        foreach ($slots as $slot) {
            $availableSlots[] = new Sheet\AvailableSlot($sheet, $slot);
        }

        $sheet->setAvailableSlots($availableSlots);

        $this->sheetRepository->set($sheet);

        if (true === $indexSheet) {
            $this->sheetIndexer->updateSheets([$sheet]);
        }
    }
}
