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
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\Sheet;

interface OrderRepositoryInterface
{
    /**
     * @param Order $order
     */
    public function add(Order $order);

    /**
     * @param Order $order
     */
    public function set(Order $order);

    /**
     * @param Sheet $sheet
     *
     * @return Order[]
     */
    public function findBySheet(Sheet $sheet);

    /**
     * @param Event  $event
     * @param array  $filters
     * @param int    $page
     * @param int    $limit
     * @param string $locale
     *
     * @return PaginatedResult[]
     */
    public function findByEvent(Event $event, array $filters, $page, $limit, $locale);
}
