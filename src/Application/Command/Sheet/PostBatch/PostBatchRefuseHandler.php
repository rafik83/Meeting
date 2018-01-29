<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\PostBatch;

use Proximum\Vimeet\Application\Adapter\SheetIndexerInterface;

class PostBatchRefuseHandler
{
    /** @var SheetIndexerInterface */
    private $sheetIndexer;

    public function __construct(SheetIndexerInterface $sheetIndexer)
    {
        $this->sheetIndexer = $sheetIndexer;
    }

    public function handle(PostBatchRefuse $postBatchRefuse)
    {
        $this->sheetIndexer->updateSheets($postBatchRefuse->sheets);
    }
}
