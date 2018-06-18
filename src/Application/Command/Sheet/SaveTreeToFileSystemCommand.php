<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;

class SaveTreeToFileSystemCommand implements Command
{
    public $uploadedObjectsTreeView;

    public function __construct(UploadedObjectsTreeView $uploadedObjectsTreeView)
    {
        $this->uploadedObjectsTreeView = $uploadedObjectsTreeView;
    }
}
