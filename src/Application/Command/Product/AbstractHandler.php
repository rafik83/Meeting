<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

abstract class AbstractHandler
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var FileStorageInterface
     */
    protected $fileStorageInterface;

    /**
     * @var UpdatePriceResolver
     */
    protected $updatePriceResolver;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param FileStorageInterface       $fileStorageInterface
     * @param UpdatePriceResolver        $updatePriceResolver
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        FileStorageInterface $fileStorageInterface = null,
        UpdatePriceResolver $updatePriceResolver
    ) {
        $this->productRepository    = $productRepository;
        $this->fileStorageInterface = $fileStorageInterface;
        $this->updatePriceResolver  = $updatePriceResolver;
    }
}
