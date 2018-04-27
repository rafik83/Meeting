<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Application\Command\Sheet\Batch\PrintPlanning;
use Proximum\Vimeet\Application\Command\Sheet\Batch\PrintPlanningHandler;
use Proximum\Vimeet\Domain\Admin\Follower\FollowerConstant;
use Proximum\Vimeet\Domain\Model\Sheet\Constant;
use Proximum\Vimeet\Domain\Model\Sheet\Group;

class BatchHandler
{
    /** @var BatchValidateHandler */
    private $batchValidateHandler;

    /** @var BatchAssignHandler */
    private $batchAssignHandler;

    /** @var BatchAcceptHandler */
    private $batchAcceptHandler;

    /** @var BatchRefuseHandler */
    private $batchRefuseHandler;

    /** @var BatchEnableDisableHandler */
    private $batchEnableDisableHandler;

    /** @var BatchCatalogHandler */
    private $batchCatalogHandler;

    /** @var BatchDraftHandler */
    private $batchDraftHandler;

    /** @var BatchValidationValidateHandler */
    private $batchValidationValidateHandler;

    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /** @var BatchGenerateInvoiceHandler */
    private $batchGenerateInvoiceHandler;

    /** @var BatchAssignToGroupHandler */
    private $batchAssignToGroupHandler;

    /** @var BatchPendingHandler */
    private $batchPendingHandler;

    /** @var BatchPdfJobCreatorHandler */
    private $batchPdfJobCreatorHandler;

    /** @var PrintPlanningHandler */
    private $printPlanningHandler;

    /**
     * @param SheetSearchAdapterInterface    $sheetSearchAdapter
     * @param BatchValidateHandler           $batchValidateHandler
     * @param BatchAssignHandler             $batchAssignHandler
     * @param BatchAcceptHandler             $batchAcceptHandler
     * @param BatchRefuseHandler             $batchRefuseHandler
     * @param BatchEnableDisableHandler      $batchEnableDisableHandler
     * @param BatchCatalogHandler            $batchCatalogHandler
     * @param BatchDraftHandler              $batchDraftHandler
     * @param BatchValidationValidateHandler $batchValidationValidateHandler
     * @param BatchGenerateInvoiceHandler    $batchGenerateInvoiceHandler
     * @param BatchAssignToGroupHandler      $batchAssignToGroupHandler
     * @param BatchPendingHandler            $batchPendingHandler
     * @param BatchPdfJobCreatorHandler      $batchPdfJobCreatorHandler
     * @param PrintPlanningHandler           $printPlanningHandler
     */
    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        BatchValidateHandler $batchValidateHandler,
        BatchAssignHandler $batchAssignHandler,
        BatchAcceptHandler $batchAcceptHandler,
        BatchRefuseHandler $batchRefuseHandler,
        BatchEnableDisableHandler $batchEnableDisableHandler,
        BatchCatalogHandler $batchCatalogHandler,
        BatchDraftHandler $batchDraftHandler,
        BatchValidationValidateHandler $batchValidationValidateHandler,
        BatchGenerateInvoiceHandler $batchGenerateInvoiceHandler,
        BatchAssignToGroupHandler $batchAssignToGroupHandler,
        BatchPendingHandler $batchPendingHandler,
        BatchPdfJobCreatorHandler $batchPdfJobCreatorHandler,
        PrintPlanningHandler $printPlanningHandler
    ) {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
        $this->batchValidateHandler = $batchValidateHandler;
        $this->batchAssignHandler = $batchAssignHandler;
        $this->batchAcceptHandler = $batchAcceptHandler;
        $this->batchRefuseHandler = $batchRefuseHandler;
        $this->batchEnableDisableHandler = $batchEnableDisableHandler;
        $this->batchCatalogHandler = $batchCatalogHandler;
        $this->batchDraftHandler = $batchDraftHandler;
        $this->batchValidationValidateHandler = $batchValidationValidateHandler;
        $this->batchGenerateInvoiceHandler = $batchGenerateInvoiceHandler;
        $this->batchAssignToGroupHandler = $batchAssignToGroupHandler;
        $this->batchPendingHandler = $batchPendingHandler;
        $this->batchPdfJobCreatorHandler = $batchPdfJobCreatorHandler;
        $this->printPlanningHandler = $printPlanningHandler;
    }

    /**
     * @param Batch $batch
     *
     * @return BatchResult
     */
    public function handle(Batch $batch): BatchResult
    {
        if (Batch::SELECTION_TYPE_ALL === $batch->selectionType) {
            $batch->ids = $this->sheetSearchAdapter->getSheetIds($batch->event, $batch->filters, $batch->locale);
        }

        if ($batch->validate) {
            return $this->batchValidateHandler->handle(new BatchValidate(
                $batch->event,
                $batch->ids,
                $batch->admin,
                $batch->validateComment
            ));
        }

        if ($batch->assign && null !== $batch->follower) {
            return $this->batchAssignHandler->handle(new BatchAssign(
                $batch->ids,
                FollowerConstant::UNASSIGNED_FOLLOWER !== $batch->follower ? $batch->follower : null
            ));
        }

        if ($batch->accept) {
            return $this->batchAcceptHandler->handle(new BatchAccept($batch->ids, $batch->admin));
        }

        if ($batch->refuse) {
            return $this->batchRefuseHandler->handle(new BatchRefuse($batch->ids, $batch->admin));
        }

        if ($batch->pending) {
            return $this->batchPendingHandler->handle(
                new BatchPending($batch->ids, $batch->admin)
            );
        }

        if ($batch->enable || $batch->disable) {
            $state = true === $batch->enable;

            return $this->batchEnableDisableHandler->handle(
                new BatchEnableDisable($batch->ids, $state, $batch->admin)
            );
        }

        if ($batch->addCatalog || $batch->removeCatalog) {
            $state = true === $batch->addCatalog;

            return $this->batchCatalogHandler->handle(
                new BatchCatalog($batch->ids, $state, $batch->admin)
            );
        }

        if ($batch->addCatalog || $batch->removeCatalog) {
            $state = true === $batch->addCatalog;

            return $this->batchCatalogHandler->handle(
                new BatchCatalog($batch->ids, $state, $batch->admin)
            );
        }

        if ($batch->draft) {
            return $this->batchDraftHandler->handle(
                new BatchDraft($batch->event, $batch->ids, $batch->admin)
            );
        }

        if ($batch->validationValidate) {
            return $this->batchValidationValidateHandler->handle(
                new BatchValidationValidate($batch->event, $batch->ids, $batch->admin)
            );
        }

        if ($batch->generateInvoice) {
            return $this->batchGenerateInvoiceHandler->handle(
                new BatchGenerateInvoice($batch->event, $batch->ids, $batch->admin)
            );
        }

        if ($batch->printPlanning && null !== $batch->printPlanningOrderBy) {
            return $this->printPlanningHandler->handle(
                new PrintPlanning(
                    $batch->event,
                    $batch->ids,
                    $batch->admin,
                    $batch->printPlanningOrderBy,
                    $batch->locale
                )
            );
        }

        if ($batch->assignToGroup && null !== $batch->group) {
            return $this->batchAssignToGroupHandler->handle(
                new BatchAssignToGroup(
                    $batch->ids,
                    $batch->group instanceof Group ? $batch->group : null,
                    $batch->locale
                )
            );
        }

        if ($batch->printPdf) {
            return $this->batchPdfJobCreatorHandler->handle(
                new BatchPdfJobCreator(
                    $batch->event,
                    $batch->ids,
                    $batch->admin,
                    $batch->locale,
                    $batch->filters['orderBy'] ?? Constant::ORDER_BY_ALPHABETICAL
                )
            );
        }

        return new BatchResult([], $batch->getMessage() . 'no_action');
    }
}
