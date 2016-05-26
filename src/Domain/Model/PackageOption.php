<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

class PackageOption
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Package
     */
    private $package;

    /**
     * @var Product
     */
    private $option;

    /**
     * @var int
     */
    private $rank;

    /**
     * PackageOption constructor.
     *
     * @param Package $package
     * @param Product          $option
     * @param int              $rank
     */
    public function __construct(Package $package, Product $option, $rank)
    {
        if (!$option->isOption()) {
            throw new \DomainException(sprintf('Product of type "%s" expected. Type "%s" given.', Product::TYPE_Option, $option->getType()));
        }

        $this->package = $package;
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
     * Get package
     *
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
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
