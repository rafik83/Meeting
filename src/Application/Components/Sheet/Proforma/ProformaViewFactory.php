<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Proforma;

use Proximum\Vimeet\Application\Components\Sheet\Order\OrderViewFactory;
use Proximum\Vimeet\Domain\Model\Order;

class ProformaViewFactory
{
    /**
     * @var OrderViewFactory
     */
    private $orderViewFactory;

    /**
     * ProformaViewFactory constructor.
     *
     * @param OrderViewFactory $orderViewFactory
     */
    public function __construct(OrderViewFactory $orderViewFactory)
    {
        $this->orderViewFactory = $orderViewFactory;
    }

    /**
     * @param Order  $order
     * @param string $locale
     *
     * @return ProformaView
     */
    public function createFromOrder(Order $order, $locale)
    {
        $orderView = $this->orderViewFactory->createFromOrder($order, $locale);

        $proforma = new ProformaView($orderView);

        return $proforma;
    }
}
