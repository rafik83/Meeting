<?php

namespace Proximum\Vimeet\Application\Command\Spot\Import;

use Proximum\Vimeet\Application\Components\Spot\SpotImporter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;

class SpotImportConfirmHandler
{
    /** @var SpotImporter */
    private $spotImporter;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /**
     * @param SpotImporter             $spotImporter
     * @param SpotRepositoryInterface  $spotRepository
     * @param SheetRepositoryInterface $sheetRepository
     */
    public function __construct(
        SpotImporter $spotImporter,
        SpotRepositoryInterface $spotRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->spotImporter = $spotImporter;
        $this->spotRepository = $spotRepository;
        $this->sheetRepository = $sheetRepository;
    }

    /**
     * @param SpotImportConfirm $command
     */
    public function handle(SpotImportConfirm $command)
    {
        $spotImports = $this->spotImporter->import(
            $command->event,
            $command->importedSpotFileName,
            $command->locale
        );

        $this->deleteSpotsByEvent($command->event);

        $sheetIds = [];
        $spots = [];

        foreach ($spotImports as $spotImport) {
            if (!$spotImport->hasError()) {
                foreach ($spotImport->sheetIds as $sheetId) {
                    $sheetIds[] = $sheetId;
                    $spots[$sheetId] = $spotImport->spot;
                }

                $this->spotRepository->add($spotImport->spot);
            }
        }

        $sheets = $this->sheetRepository->findByIds($sheetIds);

        foreach ($sheets as $sheet) {
            if (isset($spots[$sheet->getId()])) {
                $sheet->setSpot($spots[$sheet->getId()]);
                $this->sheetRepository->set($sheet);
            }
        }
    }

    /**
     * @param Event $event
     */
    private function deleteSpotsByEvent(Event $event): void
    {
        $existentSpots = $this->spotRepository->getAllByEvent($event);

        $this->spotRepository->removeBatchSpot($existentSpots, $event);
    }
}
