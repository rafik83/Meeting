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
     * @Given /^there is an order with the amount of (?P<total>\d+) and VAT is applicable$/
     *
     * @param float $total
     */
    public function thereIsAnOrderAndVatIsApplicable($total)
    {
        $this->createOrder($total, true);
    }

    /**
     * @Given /^there is an order with the amount of (?P<total>\d+) for this sheet$/
     *
     * @param float $total
     */
    public function thereIsAnOrderForThisSheet($total)
    {
        $this->createOrderForSheet($total);
    }

    /**
     * @Given /^there is an order with the amount of (?P<total>\d+) and VAT is not applicable$/
     *
     * @param float $total
     */
    public function thereIsAnOrderAndVatIsNotApplicable($total)
    {
        $this->createOrder($total, false);
    }

    /**
     * @param float $total
     * @param bool  $isVatApplicable
     *
     * @throws \Exception
     */
    private function createOrder(float $total, bool $isVatApplicable = true): void
    {
        $event = $this->orderContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $orderManager = $this->orderContextProxy->getOrderManager();

        $order = $orderManager->createOrderOfGivenTotal($event, $total, $isVatApplicable);
        $this->orderContextProxy->getStorage()->set('order', $order);
    }

    /**
     * @param float $total
     *
     * @throws \Exception
     */
    private function createOrderForSheet(float $total): void
    {
        $event = $this->orderContextProxy->getStorage()->get('event');
        $sheet = $this->orderContextProxy->getStorage()->get('sheet');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        if (null === $sheet) {
            throw new \InvalidArgumentException('Missing Sheet');
        }

        $orderManager = $this->orderContextProxy->getOrderManager();

        $order = $orderManager->createOrderOfGivenTotal($event, $total, true, $sheet);
        $this->orderContextProxy->getStorage()->set('order', $order);
    }
}
