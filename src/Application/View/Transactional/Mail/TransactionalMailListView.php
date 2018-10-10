<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Transactional\Mail;

class TransactionalMailListView
{
    /** @var MailView[] */
    public $mailViews;

    public function __construct(array $mailViews = [])
    {
        $this->mailViews = $mailViews;
    }
}
