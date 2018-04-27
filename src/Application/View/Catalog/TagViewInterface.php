<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
