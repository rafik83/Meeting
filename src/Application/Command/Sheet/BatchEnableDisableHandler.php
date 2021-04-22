<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\BatchJobQueueInterface;
use Proximum\Vimeet\Application\Command\Order\CancelAll;
use Proximum\Vimeet\Application\Command\Order\CancelAllHandler;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Exception\Order\OrderCanNotBeCancelledException;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class BatchEnableDisableHandler
{
    const STATE_ENABLE  = 'enable';
    const STATE_DISABLE = 'disable';

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
    private $batchEnableDisableJobQueue;

    /** @var CancelAllHandler */
    private $cancelAllHandler;

    public function __construct(
        CancelAllHandler $cancelAllHandler,
        SheetRepositoryInterface $sheetRepository,
        MeetingRepositoryInterface $meetingRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        BatchJobQueueInterface $batchEnableDisableJobQueue
    ) {
        $this->cancelAllHandler = $cancelAllHandler;
        $this->sheetRepository = $sheetRepository;
        $this->meetingRepository = $meetingRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->batchEnableDisableJobQueue = $batchEnableDisableJobQueue;
    }

    public function handle(BatchEnableDisable $batchEnableDisable): BatchResult
    {
        $sheets = $this->sheetRepository->getSheetsById($batchEnableDisable->ids);
        $message = (true === $batchEnableDisable->state) ? 'enable.success' : 'disable.success';
        $processedSheets = [];
        $ignoredSheets = [];
        $ignoredSheetsMessage = '';
        $meetings = [];

        if (false === $batchEnableDisable->state) {
            $meetings = $this->meetingRepository->countMeetingsOfSheetByIds($batchEnableDisable->ids);
        }

        foreach ($batchEnableDisable->ids as $index => $id) {
            if (!isset($sheets[$id])) {
                continue;
            }

            $sheet = $sheets[$id];

            $disableSheetWithMeeting = false === $batchEnableDisable->state
                && isset($meetings[$id]) && $meetings[$id] > 0;

            $enableRefusedSheet = true === $batchEnableDisable->state && $sheet->isRefused();

            if ($disableSheetWithMeeting || $enableRefusedSheet) {
                $ignoredSheets[] = $sheet;
                $this->excludeSheetFromBatch($batchEnableDisable, $index);

                continue;
            }

            $canCancelOrdersWhenSheetIsDisabled = $this->cancelOrdersWhenSheetIsDisabled(
                $sheet,
                $batchEnableDisable->admin,
                $batchEnableDisable->state
            );

            if (!$canCancelOrdersWhenSheetIsDisabled) {
                $ignoredSheets[] = $sheet;
                $this->excludeSheetFromBatch($batchEnableDisable, $index);

                continue;
            }

            $processedSheets[] = $sheet;
        }

        $processedSheetsIds = array_map(
            function (Sheet $sheet) {
                return $sheet->getId();
            },
            $processedSheets
        );

        if (!empty($processedSheetsIds)) {
            $this->sheetRepository->updateEnableStateBySheetsId($processedSheetsIds, $batchEnableDisable->state);

            $this->batchEnableDisableJobQueue->createJob(
                $processedSheetsIds,
                $batchEnableDisable->admin,
                ['state' => $batchEnableDisable->state ? self::STATE_ENABLE : self::STATE_DISABLE]
            );
        }

        if (count($ignoredSheets) > 0) {
            $message = true === $batchEnableDisable->state ? 'enable.warning' : 'disable.warning';
            $locale  = $ignoredSheets[0]->getEvent()->getAvailableLocale($batchEnableDisable->admin->getLocale());
            // Format sheets title to display them in flash warning message
            $ignoredSheetsMessage = implode(', ',
                array_map(function (Sheet $sheet) use ($locale) {
                    return $this->sheetInfoGuesser->guessSheetTitle(
                        $sheet,
                        $locale
                    );
                }, $ignoredSheets));
        }

        return new BatchResult($processedSheets, $batchEnableDisable->getMessage() . $message, $ignoredSheetsMessage);
    }

    /**
     * Remove a specific sheet id from the pull of batch IDs
     *
     * @param BatchEnableDisable $command
     * @param int                $index
     */
    private function excludeSheetFromBatch(BatchEnableDisable $command, $index)
    {
        unset($command->ids[$index]);
    }

    /**
     * return false when sheet should be disabled but orders can not be cancelled
     */
    private function cancelOrdersWhenSheetIsDisabled(Sheet $sheet, Admin $admin, bool $state): bool
    {
        if (true === $state) {
            return true;
        }

        try {
            $this->cancelAllHandler->handle(new CancelAll($sheet, $admin));
        } catch (OrderCanNotBeCancelledException $orderCanNotBeCancelledException) {
            return false;
        }

        return true;
    }
}
