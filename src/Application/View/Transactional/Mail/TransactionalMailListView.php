<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Transactional\Mail;

use Proximum\Vimeet\Application\View\Transactional\Mail\Generic\GenericMailView;

class TransactionalMailListView
{
    /** @var GenericMailView[] */
    public $genericMailViews;

    public function __construct(array $genericMailViews = [])
    {
        $this->genericMailViews = $genericMailViews;
    }
}
