<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\OrderContextProxyInterface;

class OrderContext implements Context
{
    /** @var OrderContextProxyInterface */
    private $orderContextProxy;

    /**
     * @param OrderContextProxyInterface $orderContextProxy
     */
    public function __construct(OrderContextProxyInterface $orderContextProxy)
    {
        $this->orderContextProxy = $orderContextProxy;
    }

    /**
     * @Given /^there is an order in the amount of (?P<total>\d+)$/
     *
     * @param float $total
     */
    public function thereIsAnOrder($total)
    {
        $event = $this->orderContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $orderManager = $this->orderContextProxy->getOrderManager();

        $order = $orderManager->createOrderOfGivenTotal($event, $total);
        $this->orderContextProxy->getStorage()->set('order', $order);
    }
}
