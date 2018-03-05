<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\Template\TaggedDataView;

class TemplateObject extends AbstractChild
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var Product[]
     */
    protected $buyableProducts = [];

    /**
     * @var null|Sheet
     */
    protected $sheet;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var TaggedDataView[]
     */
    protected $taggedDataViews = [];

    /**
     * @param string $key
     * @param string $type
     * @param array  $config
     * @param string $locale
     * @param string $fallback
     */
    public function __construct($key, $type, array $config, $locale, $fallback)
    {
        parent::__construct($type, $config, $locale, $fallback);

        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

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
        return $this->data ?: [];
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
     * @return array
     */
    public function getTagsWithoutSetters()
    {
        return array_diff($this->getTags(), Tag::getSetters());
    }

    /**
     * @return bool
     */
    public function hasAtLeastOneSetterTag(): bool
    {
        $tagSetters = Tag::getSetters();

        foreach ($this->getTags() as $tag) {
            if (in_array($tag, $tagSetters, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $keyTag
     */
    public function removeTag($keyTag)
    {
        if (isset($this->config['tags'])
            && is_array($this->config['tags'])
            && isset($this->config['tags'][$keyTag])
        ) {
            unset($this->config['tags'][$keyTag]);
        }
    }

    /**
     * @param string      $locale
     * @param null|string $fallback
     *
     * @return string
     */
    public function getLabel($locale, $fallback = null)
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
        if (null !== $this->getOption('products')) {
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
        return $this->getOption('help', $locale ?: $this->locale, $fallback ?: $this->fallback);
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
    public function getBuyableProducts(): array
    {
        return $this->buyableProducts;
    }

    /**
     * @param Product[]
     */
    public function setBuyableProducts(array $products)
    {
        $this->buyableProducts = $products;
    }

    /**
     * @return null|int
     */
    public function getSelectedProduct()
    {
        return isset($this->data['product']) ? $this->data['product'] : null;
    }

    /**
     * @param Product|null $selectedProduct
     */
    public function setSelectedProduct(Product $selectedProduct = null)
    {
        $this->data['product'] = $selectedProduct instanceof Product ? $selectedProduct->getId() : null;
    }

    /**
     * @return null|Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @param Sheet $sheet
     */
    public function setSheet($sheet)
    {
        $this->sheet = $sheet;
    }

    /**
     * @param TaggedDataView $taggedDataView
     */
    public function addTaggedDataView(TaggedDataView $taggedDataView)
    {
        $this->taggedDataViews[] = $taggedDataView;
    }

    /**
     * @return TaggedDataView[]
     */
    public function getTaggedDataViews()
    {
        return $this->taggedDataViews;
    }

    /**
     * @return bool
     */
    public function hasOnlyTagUrl()
    {
        if (count($this->getTags()) === 1) {
            $tag = $this->getTags()[0];

            if ($tag["tag"] === Tag::SHEET_WEBSITE) {
                return true;
            }
        }

        return false;
    }
}
