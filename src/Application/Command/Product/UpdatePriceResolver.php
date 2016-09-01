<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Product;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

class UpdatePriceResolver
{
    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * UpdatePriceResolver constructor.
     *
     * @param CartRowRepositoryInterface $cartRowRepository
     */
    public function __construct(CartRowRepositoryInterface $cartRowRepository)
    {
        $this->cartRowRepository = $cartRowRepository;
    }

    /**
     * Check if product price can be updated
     *
     * @param Product $product
     *
     * @return bool
     */
    public function resolve(Product $product)
    {
        $cartRows = $this->cartRowRepository->findByProduct($product);

        if (count($cartRows) === 0 && $product->getAvailabilityStatus()) {
            return true;
        }

        return false;
    }
}
