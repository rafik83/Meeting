<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;

class SaveTreeToFileSystemCommand implements Command
{
    /** @var UploadedObjectsTreeView */
    public $uploadedObjectsTreeView;

    public function __construct(UploadedObjectsTreeView $uploadedObjectsTreeView)
    {
        $this->uploadedObjectsTreeView = $uploadedObjectsTreeView;
    }
}
