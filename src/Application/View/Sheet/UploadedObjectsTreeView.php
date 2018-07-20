<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Sheet;

class UploadedObjectsTreeView
{
    /** @var UploadedObjectNodeView[] */
    public $tree;

    public function __construct()
    {
        $this->tree = [];
    }

    public function addNode(UploadedObjectNodeView $uploadedObjectNodeView, string $key): void
    {
        $this->tree[$key] = $uploadedObjectNodeView;
    }
}
