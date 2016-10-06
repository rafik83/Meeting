<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

class BatchHandler
{
    /**
     * @var BatchValidateHandler
     */
    private $batchValidateHandler;

    /**
     * @var BatchAssignHandler
     */
    private $batchAssignHandler;

    /**
     * @var BatchAcceptHandler
     */
    private $batchAcceptHandler;

    /**
     * @var BatchEnableDisableHandler
     */
    private $batchEnableDisableHandler;

    /**
     * @var BatchCatalogHandler
     */
    private $batchCatalogHandler;

    /**
     * BatchHandler constructor.
     *
     * @param BatchValidateHandler      $batchValidateHandler
     * @param BatchAssignHandler        $batchAssignHandler
     * @param BatchAcceptHandler        $batchAcceptHandler
     * @param BatchEnableDisableHandler $batchEnableDisableHandler
     * @param BatchCatalogHandler       $batchCatalogHandler
     */
    public function __construct(
        BatchValidateHandler $batchValidateHandler,
        BatchAssignHandler $batchAssignHandler,
        BatchAcceptHandler $batchAcceptHandler,
        BatchEnableDisableHandler $batchEnableDisableHandler,
        BatchCatalogHandler $batchCatalogHandler
    ) {
        $this->batchValidateHandler      = $batchValidateHandler;
        $this->batchAssignHandler        = $batchAssignHandler;
        $this->batchAcceptHandler        = $batchAcceptHandler;
        $this->batchEnableDisableHandler = $batchEnableDisableHandler;
        $this->batchCatalogHandler       = $batchCatalogHandler;
    }

    /**
     * @param Batch $batch
     *
     * @return BatchResult
     */
    public function handle(Batch $batch)
    {
        if ($batch->validate) {
            return $this->batchValidateHandler->handle(new BatchValidate(
                $batch->ids,
                $batch->admin,
                $batch->date,
                $batch->validateComment
            ));
        }

        if ($batch->assign && $batch->follower) {
            return $this->batchAssignHandler->handle(new BatchAssign($batch->ids, $batch->follower));
        }

        if ($batch->accept) {
            return $this->batchAcceptHandler->handle(new BatchAccept($batch->ids, $batch->admin));
        }

        if ($batch->enable || $batch->disable) {
            $state = (true === $batch->enable) ? true : false;

            return $this->batchEnableDisableHandler->handle(
                new BatchEnableDisable($batch->ids, $state, $batch->admin)
            );
        }
        if ($batch->addCatalog || $batch->removeCatalog) {
            $state = (true === $batch->addCatalog) ? true : false;

            return $this->batchCatalogHandler->handle(
                new BatchCatalog($batch->ids, $batch->date, $state, $batch->admin)
            );
        }
        if ($batch->addCatalog || $batch->removeCatalog) {
            $state = (true === $batch->addCatalog) ? true : false;

            return $this->batchCatalogHandler->handle(
                new BatchCatalog($batch->ids, $batch->date, $state, $batch->admin)
            );
        }

        return new BatchResult(0, $batch->getMessage() . 'no_action');
    }
}
