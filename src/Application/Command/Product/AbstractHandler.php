<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Product\UpdatePriceResolver;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

abstract class AbstractHandler
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var FileStorageInterface */
    protected $fileStorageInterface;

    /** @var UpdatePriceResolver */
    protected $updatePriceResolver;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param UpdatePriceResolver        $updatePriceResolver
     * @param FileStorageInterface       $fileStorageInterface
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        UpdatePriceResolver $updatePriceResolver,
        FileStorageInterface $fileStorageInterface = null
    ) {
        $this->productRepository    = $productRepository;
        $this->fileStorageInterface = $fileStorageInterface;
        $this->updatePriceResolver  = $updatePriceResolver;
    }
}
