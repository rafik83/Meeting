<?php

namespace Proximum\Vimeet\Application\Command\Template\Registration;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Application\Command\UserEventView\Update as UpdateUserEvent;

/**
 * Reindex all sheets by given SheetTemplate
 */
class IndexHandler
{
    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var CommandBusInterface */
    private $commandBus;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        SheetIndexerInterface $sheetIndexer,
        CommandBusInterface $commandBus
    ) {
        $this->sheetIndexer = $sheetIndexer;
        $this->sheetRepository = $sheetRepository;
        $this->commandBus = $commandBus;
    }

    public function handle(Index $index): void
    {
        $sheets = $this->sheetRepository->getByRegistrationTemplate($index->registrationTemplate);
        $this->sheetIndexer->updateSheets($sheets);

        foreach ($sheets as $sheet) {
            foreach ($sheet->getParticipants() as $participant) {
                $this->commandBus->handle(
                    new UpdateUserEvent(
                        $participant->getUser(),
                        $participant->getEvent()
                    )
                );
            }
        }
    }
}
