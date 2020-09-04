<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\Context\Domain\Product;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Domain\Proxy\ProductContextProxyInterface;
use Proximum\Vimeet\Domain\Model\Event;

class ProductContext implements Context
{
    /** @var ProductContextProxyInterface */
    private $productContextProxy;

    /**
     * @param ProductContextProxyInterface $productContextProxy
     */
    public function __construct(ProductContextProxyInterface $productContextProxy)
    {
        $this->productContextProxy = $productContextProxy;
    }

    /**
     * @Given /^there is a product Participant called "(?P<title>[^"]+)" with a price of "(?P<unitPrice>[^"]+)" and a max quantity of (?P<maxQuantity>\d+)$/
     *
     * @param string $title
     * @param float  $unitPrice
     * @param int    $maxQuantity
     */
    public function createParticipantProduct(
        string $title,
        float $unitPrice,
        int $maxQuantity
    ) {
        $event = $this->productContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $product = $this->productContextProxy
            ->getProductManager()
            ->createParticipant($event, $title, $unitPrice, $maxQuantity)
        ;

        $this->productContextProxy->getStorage()->set('productParticipant', $product);
    }

    /**
     * @Given /^there is a product Planning called "(?P<title>[^"]+)" with a price of "(?P<unitPrice>[^"]+)" and a max quantity of (?P<maxQuantity>\d+)$/
     *
     * @param string $title
     * @param float  $unitPrice
     * @param int    $maxQuantity
     */
    public function createPlanningProduct(
        string $title,
        float $unitPrice,
        int $maxQuantity
    ) {
        $event = $this->productContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $product = $this->productContextProxy
            ->getProductManager()
            ->createPlanning($event, $title, $unitPrice, 20, $maxQuantity)
        ;

        $this->productContextProxy->getStorage()->set('productPlanning', $product);
    }

    /**
     * @Given /^there is a plan called "(?P<title>[^"]+)" with a price of "(?P<unitPrice>[^"]+)"$/
     *
     * @param string $title
     * @param float  $unitPrice
     */
    public function createPlan(
        string $title,
        float $unitPrice
    ) {
        $event = $this->productContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $product =$this->productContextProxy
            ->getProductManager()
            ->createPlan($event, $title, $unitPrice)
        ;

        $this->productContextProxy->getStorage()->set('plan', $product);
    }

    /**
     * @Given /^there is a planning called "(?P<title>[^"]+)" with a price of "(?P<quantity>[^"]+)"$/
     *
     * @param string $title
     * @param float  $unitPrice
     */
    public function createPlanning(string $title, float $unitPrice)
    {
        $event = $this->productContextProxy->getStorage()->get('event');

        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $product = $this->productContextProxy->getProductManager()->createPlanning($event, $title, $unitPrice);

        $this->productContextProxy->getStorage()->set('planning', $product);
    }

    /**
     * @Given /^this plan includes this product participant (?P<quantity>\d+) times?$/
     *
     * @param int $quantity
     */
    public function assignProductParticipantToPlan(int $quantity)
    {
        $plan = $this->productContextProxy->getStorage()->get('plan');
        $productParticipant = $this->productContextProxy->getStorage()->get('productParticipant');

        if (null === $plan) {
            throw new \InvalidArgumentException('Missing Plan');
        }

        if (null === $productParticipant) {
            throw new \InvalidArgumentException('Missing Product Participant');
        }

        $this->productContextProxy
            ->getProductManager()
            ->assignProductParticipantToPlan($plan, $productParticipant, $quantity)
        ;
    }

    /**
     * @Given /^there is an option called "(?P<title>[^"]+)" with a price of "(?P<unitPrice>[^"]+)"$/
     */
    public function thereIsAnOptionWithAPrice(string $title, float $unitPrice)
    {
        $this->addOption($title, $unitPrice);
    }

    /**
     * @Given /^there is an attributable option called "(?P<title>[^"]+)" with a price of "(?P<unitPrice>[^"]+)"$/
     */
    public function thereIsAnAttributableOptionWithAPrice(string $title, float $unitPrice)
    {
        $this->addOption($title, $unitPrice, true);
    }

    /**
     * @Given /^there is an attributable option called "(?P<title>[^"]+)" with a quantity max of "(?P<quantityMax>[^"]+)" and a price of "(?P<unitPrice>[^"]+)"$/
     */
    public function thereIsAnAttributableOptionWithAPriceAndAQuantityMax(
        string $title,
        float $unitPrice,
        int $quantityMax
    ) {
        $this->addOption($title, $unitPrice, true, $quantityMax);
    }

    private function addOption(string $title, $unitPrice, bool $isAttributable = false, $quantityMax = null)
    {
        $event = $this->productContextProxy->getStorage()->get('event');

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $options = $this->productContextProxy->getStorage()->get('options');

        $options[] = $this->productContextProxy
            ->getProductManager()
            ->createOption(
                $event,
                $title,
                $unitPrice,
                20,
                $quantityMax,
                null,
                null,
                null,
                false,
                null,
                $isAttributable
            )
        ;

        $this->productContextProxy->getStorage()->set('options', $options);
    }
}
