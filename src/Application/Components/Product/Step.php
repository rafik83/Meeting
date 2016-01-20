<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product;

use Proximum\Vimeet\Application\Components\Product\Products\ProductInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Step
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    public $options;

    /**
     * @var ProductInterface[]
     */
    public $products;

    /**
     * @var Template
     */
    public $template;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @param ProductInterface $product
     */
    public function addProduct(ProductInterface $product)
    {
        $this->products[$product->getKey()] = $product;
        $product->setStep($this);
    }

    /**
     * @param OptionsResolver $optionsResolver
     */
    public function configure(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['label', 'template']);
        $optionsResolver->setDefined(['description']);
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getLabel($locale)
    {
        return isset($this->options['label'][$locale]) ? $this->options['label'][$locale] : null;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return isset($this->options['description'])
            && isset($this->options['description'][$locale])
            ? $this->options['description'][$locale] : null;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return Products\ProductInterface[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param Products\ProductInterface[] $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @param string $key
     *
     * @return null|ProductInterface
     */
    public function getProduct($key)
    {
        foreach ($this->products as $product) {
            if ($product->getKey() === $key) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @param Template $template
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;
    }

    /**
     * @return Template $template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return bool
     */
    public function hasProducts()
    {
        return !empty($this->products);
    }

    /**
     * @param ProductInterface $product
     */
    public function removeProduct(ProductInterface $product)
    {
        unset ($this->products[$product->getKey()]);
    }
}
