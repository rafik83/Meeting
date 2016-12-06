<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Unavailability;

use Proximum\Vimeet\Domain\Model\AbstractCategory;
use Proximum\Vimeet\Domain\Model\Event;

class Category extends AbstractCategory
{
    /**
     * @var string
     */
    private $title;

    /**
     * Category constructor.
     *
     * @param Event  $event
     * @param string $picto
     * @param string $title
     */
    public function __construct(Event $event, $picto, $title)
    {
        parent::__construct($event, $picto);

        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $picto
     * @param string $title
     */
    public function update($picto, $title)
    {
        $this->picto = $picto;
        $this->title = $title;
    }
}
