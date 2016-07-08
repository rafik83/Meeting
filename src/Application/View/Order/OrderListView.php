<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\Order;

class OrderListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $follower;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * @var float
     */
    public $total;

    /**
     * @var string
     */
    public $vatMode;

    /**
     * @var string
     */
    public $currency;

    /**
     * @param int                $id
     * @param string             $title
     * @param string             $type
     * @param string             $follower
     * @param \DateTimeInterface $createdAt
     * @param float              $total
     * @param string             $vatMode
     * @param string             $currency
     */
    public function __construct(
        $id,
        $title,
        $type,
        $follower,
        \DateTimeInterface $createdAt,
        $total,
        $vatMode,
        $currency
    ) {
        $this->id        = $id;
        $this->title     = $title;
        $this->type      = $type;
        $this->follower  = $follower;
        $this->createdAt = $createdAt;
        $this->total     = $total;
        $this->vatMode   = $vatMode;
        $this->currency  = $currency;
    }
}
