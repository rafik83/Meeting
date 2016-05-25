<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class PurchasingFunnelOption
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
    private $option;

    /**
     * @var int
     */
    private $rank;

    /**
     * PurchasingFunnelOption constructor.
     *
     * @param PurchasingFunnel $purchasingFunnel
     * @param Product          $option
     * @param int              $rank
     */
    public function __construct(PurchasingFunnel $purchasingFunnel, Product $option, $rank)
    {
        if (!$option->isOption()) {
            throw new \DomainException(sprintf('Product of type "%s" expected. Type "%s" given.', Product::TYPE_Option, $option->getType()));
        }

        $this->purchasingFunnel = $purchasingFunnel;
        $this->option           = $option;
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
     * Get option
     *
     * @return Product
     */
    public function getOption()
    {
        return $this->option;
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
}
