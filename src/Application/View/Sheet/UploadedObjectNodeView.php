<?php

namespace Proximum\Vimeet\Application\View\Sheet;

class UploadedObjectNodeView
{
    /** @var string */
    public $folder;

    /** @var UploadedObjectView[] */
    public $uploadedObjectsView;

    public function __construct(string $folder)
    {
        $this->folder = $folder;
    }

    public function addUploadedObjectView(UploadedObjectView $uploadedObjectView): void
    {
        $this->uploadedObjectsView[] = $uploadedObjectView;
    }
}
