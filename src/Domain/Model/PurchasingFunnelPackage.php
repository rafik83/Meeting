<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class PurchasingFunnelPackage
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
    private $package;

    /**
     * @var int
     */
    private $rank;

    /**
     * PurchasingFunnelPackage constructor.
     *
     * @param PurchasingFunnel $purchasingFunnel
     * @param Product          $package
     * @param int              $rank
     */
    public function __construct(PurchasingFunnel $purchasingFunnel, Product $package, $rank)
    {
        if (!$package->isPackage()) {
            throw new \DomainException(sprintf('Product of type "%s" expected. Type "%s" given.', Product::TYPE_PACKAGE, $package->getType()));
        }

        $this->purchasingFunnel = $purchasingFunnel;
        $this->package          = $package;
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
     * Get package
     *
     * @return Product
     */
    public function getPackage()
    {
        return $this->package;
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
     * @return PurchasingFunnelPackage
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }
}
