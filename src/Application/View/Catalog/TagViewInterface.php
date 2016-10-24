<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Catalog;

interface TagViewInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @return string
     */
    public function getTitle();
}
