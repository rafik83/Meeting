<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Invoice;

use Proximum\Vimeet\Domain\Model\Event;

class Prefix
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $prefix;

    /**
     * Prefix constructor.
     *
     * @param string $title
     * @param string $prefix
     */
    public function __construct($title, $prefix)
    {
        $this->title  = $title;
        $this->prefix = $prefix;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
