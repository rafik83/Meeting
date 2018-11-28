<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Admin;

class AdminListView
{
    /** @var AdminView[] */
    public $adminViews;

    public function __construct(array $adminViews = [])
    {
        $this->adminViews = $adminViews;
    }
}
