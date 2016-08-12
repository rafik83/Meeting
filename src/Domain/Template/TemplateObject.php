<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Product;

class TemplateObject extends AbstractChild
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var Product[]
     */
    private $buyableProducts;

    /**
     * {@inheritdoc}
     */
    public function getComponent()
    {
        return 'object';
    }

    /**
     * Set data
     *
     * @param array $data
     *
     * @return TemplateObject
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }
    
    /**
     * {@inheritdoc}
     */
    public function normalize()
    {
        return [
            'component' => 'object',
            'type'      => $this->type,
            'config'    => $this->config,
        ];
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data ? : [];
    }

    /**
     * @param string $tag
     *
     * @return bool
     */
    public function hasTag($tag)
    {
        return isset($this->config['tags']) && is_array($this->config['tags']) && in_array($tag, $this->config['tags']);
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return isset($this->config['tags']) && is_array($this->config['tags']) ? $this->config['tags'] : [];
    }

    /**
     * @param string $locale
     * @param string $fallback
     *
     * @return string
     */
    public function getLabel($locale, $fallback)
    {
        return $this->getOption('label', $locale, $fallback);
    }

    /**
     * @return string|null
     */
    public function getDefaultLabel()
    {
        return $this->getOption('label', $this->locale);
    }

    /**
     * @return bool
     */
    public function isTranslatable()
    {
        return $this->getOption('translatable') === true;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->getData();
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isBuyable()
    {
        return null !== $this->getOption('products');
    }

    /**
     * @return string|null
     */
    public function getPlaceholder()
    {
        return $this->getOption('placeholder', $this->locale);
    }

    /**
     * Array of product ids
     *
     * @return null|array
     */
    public function getProducts()
    {
        if(null !== $this->getOption('products')) {
            return array_values($this->getOption('products'));
        }

        return null;
    }

    /**
     * @param null|string $locale
     * @param null|string $fallback
     *
     * @return string|null
     */
    public function getHelp($locale = null, $fallback = null)
    {
        return $this->getOption('help', $locale ? : $this->locale, $fallback ? : $this->fallback);
    }

    /**
     * @return bool
     */
    public function getRequired()
    {
        return null !== $this->getOption('required') ? $this->getOption('required') : false;
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->getOption('tag');
    }

    /**
     * @return Product[]
     */
    public function getBuyableProducts()
    {
        return $this->buyableProducts;
    }

    /**
     * @param Product[]
     */
    public function setBuyableProducts($products)
    {
        $this->buyableProducts = $products;
    }
}
