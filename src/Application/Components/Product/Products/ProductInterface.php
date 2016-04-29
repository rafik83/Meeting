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
use Proximum\Vimeet\Application\Components\Product\Step;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ProductInterface
{
    /**
     * @return string
     */
    public function getKey();

    /**
     * @param OptionsResolver $optionsResolver
     */
    public function configure(OptionsResolver $optionsResolver);

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getLabel($locale);

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getDescription($locale);

    /**
     * @return string
     */
    public function getType();

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @return string
     */
    public function getRequired();

    /**
     * @return float
     */
    public function getUnitPrice();

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

    /**
     * @return Including[]
     */
    public function getIncludedIn();

    /**
     * @return Including[]
     */
    public function getInclude();

    /**
     * @param Step $step
     */
    public function setStep(Step $step);

    /**
     * @return Step $step
     */
    public function getStep();

    /**
     * Get all the products bought where this product is include
     *
     * @param array $packageData
     */
    public function getIncludingFromPurchase(array $packageData);

    /**
     * @param array $options
     */
    public function setOptions(array $options);

    /**
     * @return array
     */
    public function getOptionsIncludedIn();
}
