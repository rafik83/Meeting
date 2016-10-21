<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetValidationValidateEvent;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;

class BatchValidationValidateHandler
{
    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $datetime;

    /**
     * BatchValidationValidateHandler constructor.
     *
     * @param SheetRepositoryInterface $sheetRepository
     * @param DelayedEventDispatcher   $eventDispatcher
     * @param \DateTimeInterface       $datetime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $datetime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->datetime        = $datetime;
    }

    /**
     * @param BatchValidationValidate $batch
     *
     * @return BatchResult
     */
    public function handle(BatchValidationValidate $batch)
    {
        $sheets = $this->sheetRepository->getSheetsById($batch->ids);

        foreach ($sheets as $sheet) {
            if (!$sheet->getValidationState() !== Sheet::STATE_VALIDATION_VALIDATED) {
                $sheet->setValidationState(Sheet::STATE_VALIDATION_VALIDATED);
                $this->sheetRepository->set($sheet);

                $this->eventDispatcher->dispatch(
                    Events::SHEET_VALIDATION_VALIDATE,
                    new SheetValidationValidateEvent(
                        $sheet,
                        $batch->admin,
                        $this->datetime
                    )
                );
            }
        }

        return new BatchResult(count($sheets), $batch->getMessage() . 'validation.validate.success');
    }
}
