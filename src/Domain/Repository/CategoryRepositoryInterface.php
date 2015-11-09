<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\CategoryView;

interface CategoryRepositoryInterface
{
    /**
     * @param Event|int $event
     * @param string    $locale
     *
     * @return CategoryView[]
     */
    public function getCategoryViewsByEvent($event, $locale);
}
