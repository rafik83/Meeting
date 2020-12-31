<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Agenda;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\Agenda\ConfirmationCalculator;

class UpdateAgendaConfirmedStatusHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var ConfirmationCalculator */
    private $confirmationCalculator;

    /**
     * @param SheetRepositoryInterface $sheetRepository
     * @param ConfirmationCalculator   $confirmationCalculator
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ConfirmationCalculator $confirmationCalculator
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->confirmationCalculator = $confirmationCalculator;
    }

    /**
     * @param UpdateAgendaConfirmedStatus $command
     */
    public function handle(UpdateAgendaConfirmedStatus $command)
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($command->user, $command->event);

        foreach ($sheets as $sheet) {
            $status = $this->confirmationCalculator->getConfirmationStatusForSheet($sheet);

            if ($status !== $sheet->getAgendaConfirmedStatus()) {
                $sheet->setAgendaConfirmedStatus($status);
                $this->sheetRepository->set($sheet);
            }
        }
    }
}
