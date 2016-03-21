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
     * BatchHandler constructor.
     *
     * @param BatchValidateHandler $batchValidateHandler
     * @param BatchAssignHandler   $batchAssignHandler
     * @param BatchAcceptHandler   $batchAcceptHandler
     */
    public function __construct(
        BatchValidateHandler $batchValidateHandler,
        BatchAssignHandler $batchAssignHandler,
        BatchAcceptHandler $batchAcceptHandler
    ) {
        $this->batchValidateHandler = $batchValidateHandler;
        $this->batchAssignHandler   = $batchAssignHandler;
        $this->batchAcceptHandler   = $batchAcceptHandler;
    }

    /**
     * @param Batch $batch
     *
     * @return BatchResult
     */
    public function handle(Batch $batch)
    {
        if ($batch->validate) {
            return $this->batchValidateHandler->handle(new BatchValidate($batch->ids));
        }

        if ($batch->assign && $batch->follower) {
            return $this->batchAssignHandler->handle(new BatchAssign($batch->ids, $batch->follower));
        }

        if ($batch->accept) {
            return $this->batchAcceptHandler->handle(new BatchAccept($batch->ids, $batch->admin, $batch->date));
        }

        return new BatchResult(0);
    }
}
