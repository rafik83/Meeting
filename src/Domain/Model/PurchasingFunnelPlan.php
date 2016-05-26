<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class PurchasingFunnelPlan
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var PurchasingFunnel
     */
    private $purchasingFunnel;

    /**
     * @var Product
     */
    private $plan;

    /**
     * @var int
     */
    private $rank;

    /**
     * PurchasingFunnelPlan constructor.
     *
     * @param PurchasingFunnel $purchasingFunnel
     * @param Product          $plan
     * @param int              $rank
     */
    public function __construct(PurchasingFunnel $purchasingFunnel, Product $plan, $rank)
    {
        if (!$plan->isPlan()) {
            throw new \DomainException(sprintf('Product of type "%s" expected. Type "%s" given.', Product::TYPE_PLAN, $plan->getType()));
        }

        $this->purchasingFunnel = $purchasingFunnel;
        $this->plan          = $plan;
        $this->rank             = $rank;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get purchasingFunnel
     *
     * @return PurchasingFunnel
     */
    public function getPurchasingFunnel()
    {
        return $this->purchasingFunnel;
    }

    /**
     * Get plan
     *
     * @return Product
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Get rank
     *
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set rank
     *
     * @param int $rank
     *
     * @return PurchasingFunnelPlan
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }
}
