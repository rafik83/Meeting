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
     * BatchHandler constructor.
     *
     * @param BatchValidateHandler $batchValidateHandler
     * @param BatchAssignHandler   $batchAssignHandler
     */
    public function __construct(BatchValidateHandler $batchValidateHandler, BatchAssignHandler $batchAssignHandler)
    {
        $this->batchValidateHandler = $batchValidateHandler;
        $this->batchAssignHandler   = $batchAssignHandler;
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

        return new BatchResult(0);
    }
}
