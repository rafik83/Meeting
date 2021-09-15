<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchCatalogHandler
{
    const ADD_CATALOG    = 'add';
    const REMOVE_CATALOG = 'remove';

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var MeetingRepositoryInterface
     */
    private $meetingRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var BatchJobQueueInterface
     */
    private $batchJobQueue;

    /**
     * BatchCatalogHandler constructor.
     *
     * @param SheetRepositoryInterface   $sheetRepository
     * @param MeetingRepositoryInterface $meetingRepository
     * @param SheetInfoGuesser           $sheetInfoGuesser
     * @param BatchJobQueueInterface     $batchJobQueue
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        MeetingRepositoryInterface $meetingRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        BatchJobQueueInterface $batchJobQueue
    ) {
        $this->sheetRepository   = $sheetRepository;
        $this->meetingRepository = $meetingRepository;
        $this->sheetInfoGuesser  = $sheetInfoGuesser;
        $this->batchJobQueue     = $batchJobQueue;
    }

    /**
     * @param BatchCatalog $command
     *
     * @return BatchResult
     */
    public function handle(BatchCatalog $command)
    {
        $sheets               = $this->sheetRepository->getSheetsById($command->ids);
        $ignoredSheets        = [];
        $ignoredSheetsMessage = '';
        $message              = ($command->state) ? 'catalog.add.success' : 'catalog.remove.success';

        $meetings = $this->meetingRepository->countMeetingsOfSheetByIds($command->ids);

        foreach ($command->ids as $index => $id) {
            if (isset($sheets[$id])) {
                $sheet = $sheets[$id];
                // If try to remove from catalog
                if (false === $command->state) {
                    if (isset($meetings[$id]) && $meetings[$id] > 0) {
                        $ignoredSheets[] = $sheet;
                        $this->excludeSheetFromBatch($command, $index);
                    }
                } elseif (true === $command->state) {
                    if (!$sheet->isEnabled() || $sheet->isRefused()) {
                        $ignoredSheets[] = $sheet;
                        $this->excludeSheetFromBatch($command, $index);
                    }
                }
            }
        }

        // update sheets in catalog state and set in catalog date
        $this->sheetRepository->updateInCatalogBySheetsId($command->ids, $command->state);

        if (count($ignoredSheets) > 0) {
            $message = $command->state ? 'catalog.add.warning' : 'catalog.remove.warning';
            $locale  = $ignoredSheets[0]->getEvent()->getAvailableLocale($command->admin->getLocale());

            // Format sheets title to display them in flash warning message
            $ignoredSheetsMessage = implode(', ', array_map(function (Sheet $sheet) use ($locale) {
                return $this->sheetInfoGuesser->guessSheetTitle(
                    $sheet,
                    $locale
                );
            }, $ignoredSheets));
        }

        if (!empty($command->ids)) {
            $this->batchJobQueue->createJob($command->ids, $command->admin, [
                'state' => $command->state ? self::ADD_CATALOG : self::REMOVE_CATALOG,
            ]);
        }

        return new BatchResult($sheets, $command->getMessage() . $message, $ignoredSheetsMessage);
    }

    /**
     * Remove a specific sheet id from the pull of batch IDs
     *
     * @param BatchCatalog $command
     * @param int          $index
     */
    private function excludeSheetFromBatch(BatchCatalog $command, $index)
    {
        unset($command->ids[$index]);
    }
}
