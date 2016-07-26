<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;

class Row
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var string
     */
    protected $data = '';

    /**
     * @var null|Product
     */
    protected $product;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * @var float
     */
    protected $price;

    /**
     * @var null|int
     */
    protected $groupId;

    /**
     * @var null|Row
     */
    protected $parentRow;

    /**
     * @var string
     */
    protected $label;

    /**
     * Row constructor.
     *
     * @param Order        $order
     * @param null|Product $product
     * @param int          $quantity
     * @param null|int     $groupId
     * @param string       $label
     * @param float        $price
     * @param null|Row     $parentRow
     */
    public function __construct(
        Order $order,
        $product,
        $quantity,
        $groupId = null,
        $label = null,
        $price = null,
        $parentRow = null
    )
    {
        $this->order       = $order;
        $this->quantity    = $quantity;
        $this->groupId     = $groupId;
        $this->label       = $label;
        $this->price       = $price;
        $this->parentRow   = $parentRow;

        if ($product != null) {
            $this->product = $product;
            $this->data    = $product->getSerializedData();
            $this->price   = $product->getUnitPrice();
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return null|int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        $data = json_decode($this->data, true);

        return isset($data['type']) ? $data['type'] : null;
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        $data = json_decode($this->data, true);

        return isset($data['id']) ? $data['id'] : null;
    }

    /**
     * @param string      $locale
     * @param string|null $fallback
     *
     * @return string
     */
    public function getLabelFromData($locale, $fallback = null)
    {
        $data = json_decode($this->data, true);

        if (!isset($data['translations'])) {
            return '';
        }

        if (isset($data['translations'][$locale]) && isset($data['translations'][$locale]['title'])) {
            return $data['translations'][$locale]['title'];
        }

        if (null !== $fallback
            && isset($data['translations'][$fallback])
            && isset($data['translations'][$fallback]['title'])
        ) {
            return $data['translations'][$fallback]['title'];
        }

        return '';
    }

    /**
     * @param string      $locale
     * @param null|string $fallback
     *
     * @return string
     */
    public function getLabel($locale = null, $fallback = null)
    {
        if (!empty($this->data) && null !== $locale) {
            return $this->getLabelFromData($locale, $fallback);
        }

        return $this->label;
    }

    /**
     * @return null|Row
     */
    public function getParentRow()
    {
        return $this->parentRow;
    }

    /**
     * @return bool
     */
    public function hasParentRow()
    {
        return null !== $this->parentRow;
    }

    /**
     * @param Order    $order
     * @param int      $quantity
     * @param null|int $groupId
     * @param string   $label
     * @param float    $price
     *
     * @return Row
     */
    public static function createCustomRowToGroup(
        Order $order,
        $quantity,
        $groupId,
        $label,
        $price
    ) {
        return new self(
            $order,
            null,
            $quantity,
            $groupId,
            $label,
            $price
        );
    }

    /**
     * @param Order  $order
     * @param Row    $parentRow
     * @param string $label
     * @param int    $quantity
     * @param float  $price
     *
     * @return Row
     */
    public static function createCustomRowToProduct(
        Order $order,
        Row $parentRow,
        $label,
        $quantity,
        $price
    ) {
        return new self(
            $order,
            null,
            $quantity,
            $parentRow->getGroupId(),
            $label,
            $price,
            $parentRow
        );
    }

    /**
     * @return bool
     */
    public function hasIncludedProduct()
    {
        $data = json_decode($this->data, true);

        return isset($data['productsIncluded']) && !empty($data['productsIncluded']) ? true : false;
    }

    /**
     * @return bool
     */
    public function isProduct()
    {
        return null !== $this->getProduct();
    }

    /**
     * @param string $label
     * @param float  $price
     * @param int    $quantity
     */
    public function update($label, $price, $quantity)
    {
        $this->label    = $label;
        $this->price    = $price;
        $this->quantity = $quantity;
    }
}
