<?php

namespace Proximum\Vimeet\Domain\Model\Order;

use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Product;

/**
 * Line of a product on an order
 */
class Row
{
    /** @var int */
    private $id;

    /** @var Order */
    private $order;

    /** @var string */
    private $data = '';

    /** @var null|Product */
    private $product;

    /** @var int */
    private $quantity;

    /**
     * "Prix unitaire"
     * "Unit price"
     *
     * @var float
     */
    private $price;

    /** @var null|int */
    private $groupId;

    /** @var null|Row */
    private $parentRow;

    /** @var string */
    private $label;

    /** @var float */
    private $vatRate;

    /**
     * @param Order        $order
     * @param int          $quantity
     * @param float        $vatRate
     * @param null|Product $product
     * @param null|int     $groupId
     * @param string       $label
     * @param float        $price
     * @param null|Row     $parentRow
     */
    public function __construct(
        Order $order,
        $quantity,
        float $vatRate,
        Product $product = null,
        $groupId = null,
        $label = null,
        float $price = null,
        $parentRow = null
    ) {
        $this->order       = $order;
        $this->quantity    = $quantity;
        $this->groupId     = $groupId;
        $this->label       = $label;
        $this->price       = $price;
        $this->vatRate     = $vatRate;
        $this->parentRow   = $parentRow;

        if (null !== $product) {
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
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     *
     * @return Order\Row
     */
    public function setOrder(Order $order): Row
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return null|Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return Row
     */
    public function setQuantity($quantity): Row
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getPriceWithVat(): float
    {
        return $this->price * (1 + $this->vatRate * 0.01);
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
    public function getParentRow(): ?Row
    {
        return $this->parentRow;
    }

    /**
     * @return bool
     */
    public function hasParentRow(): bool
    {
        return null !== $this->parentRow;
    }

    public function detachParentRow(): void
    {
        $this->parentRow = null;
    }

    /**
     * @return float
     */
    public function getVatRate(): float
    {
        return $this->vatRate;
    }

    /**
     * @param Order    $order
     * @param int      $quantity
     * @param null|int $groupId
     * @param string   $label
     * @param float    $price
     * @param float    $vatRate
     *
     * @return Row
     */
    public static function createCustomRowToGroup(
        Order $order,
        $quantity,
        $groupId,
        $label,
        $price,
        float $vatRate
    ): Row {
        return new self(
            $order,
            $quantity,
            $vatRate,
            null,
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
     * @param float  $vatRate
     *
     * @return Row
     */
    public static function createCustomRowToProduct(
        Order $order,
        Row $parentRow,
        $label,
        $quantity,
        $price,
        float $vatRate
    ): Row {
        return new self(
            $order,
            $quantity,
            $vatRate,
            null,
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

    public function isCustomRow(): bool
    {
        return null === $this->product;
    }
}
