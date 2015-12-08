<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product\Products;

use Proximum\Vimeet\Application\Components\Product\Including;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ProductInterface
{
    /**
     * @param OptionsResolver $optionsResolver
     */
    public function configure(OptionsResolver $optionsResolver);

    /**
     * @param ProductInterface $includer
     * @param ProductInterface $include
     * @param float            $quantity
     */
    public function including(ProductInterface $includer, ProductInterface $include, $quantity);

    /**
     * @param Including $including
     */
    public function addIncludedIn(Including $including);

    /**
     * @param Including $including
     */
    public function addInclude(Including $including);
}
