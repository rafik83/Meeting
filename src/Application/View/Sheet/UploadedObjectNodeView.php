<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

use Behat\Transliterator\Transliterator;

class UploadedObjectNodeView
{
    /** @var string */
    public $folder;

    /** @var UploadedObjectView[] */
    public $uploadedObjectsView;

    public function __construct(string $key, string $label)
    {
        $this->folder = Transliterator::urlize($key.'-'.$label);
    }

    public function addUploadedObjectView(UploadedObjectView $uploadedObjectView): void
    {
        $this->uploadedObjectsView[] = $uploadedObjectView;
    }
}
