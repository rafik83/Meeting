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
            'required',
            'description',
        ]);
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getLabel($locale)
    {
        return isset($this->options['label'][$locale]) ? $this->options['label'][$locale] : null;
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
     * @return float|null
     */
    public function getQuantityMin()
    {
        return isset($this->options['quantity'])
            && isset($this->options['quantity']['min'])
            ? $this->options['quantity']['min'] : null;
    }

    /**
     * @return float|null
     */
    public function getQuantityMax()
    {
        return isset($this->options['quantity'])
            && isset($this->options['quantity']['max'])
            ? $this->options['quantity']['max'] : null;
    }

    /**
     * @return float|null
     */
    public function getQuantityRange()
    {
        return isset($this->options['quantity'])
            && isset($this->options['quantity']['range'])
            ? $this->options['quantity']['range'] : 1;
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->options['unitPrice'];
    }
}
