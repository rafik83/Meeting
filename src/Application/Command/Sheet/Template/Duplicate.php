<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\Template;

class Duplicate
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var Template
     */
    public $template;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var DateTimeInterface
     */
    public $createdAt;

    /**
     * Duplicate constructor.
     *
     * @param Template          $template
     * @param DateTimeInterface $createdAt
     */
    public function __construct(Template $template, DateTimeInterface $createdAt)
    {
        $this->createdAt = $createdAt;
        $this->template  = $template;
    }
}
