<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product\Products;

use Symfony\Component\OptionsResolver\OptionsResolver;

class LibOptionProduct extends AbstractProduct
{
    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $optionsResolver)
    {
        parent::configure($optionsResolver);

        $optionsResolver->setRequired(['label', 'type', 'unitPrice']);
        $optionsResolver->setDefined([
            'quantity',
            'description',
        ]);
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getDescription($locale)
    {
        return isset($this->options['description'][$locale]) ? $this->options['description'][$locale] : null;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * @return string
     */
    public function getRequired()
    {
        return isset($this->options['required']) ? $this->options['required'] : false;
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->options['unitPrice'];
    }

    /**
     * @param array $packageData
     * @param array $data
     *
     * @return bool
     */
    public function isAvailableToPurchase(array $packageData, array $data)
    {
        if (null === $this->getQuantityIncludedWithPurchase($packageData)) {
            return false;
        }

        if (empty($data) || !isset($data['value']) || false === $data['value']) {
            return true;
        }

        if ($this->hasQuantity() && $this->getRemainingQuantityMax($packageData) > 0) {
            return true;
        }

        return false;
    }
}
