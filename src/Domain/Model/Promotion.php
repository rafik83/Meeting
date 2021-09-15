<?php

namespace Proximum\Vimeet\Domain\Model;

class Promotion
{
    const TYPE_PERCENT_OFF = 'percent_off';
    const TYPE_VALUE_OFF   = 'value_off';
    const TYPE_FREE        = 'free';

    /**
     * @var int
     */
    private $id;

    /**
     * @var PromotionCode
     */
    private $promotionCode;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var string
     */
    private $type;

    /**
     * @var float
     */
    private $value;

    /**
     * @var int
     */
    private $quantityMax;

    /**
     * Promotion constructor.
     *
     * @param PromotionCode $promotionCode
     * @param Product       $product
     * @param string        $type
     * @param float         $value
     * @param null|int      $quantityMax
     */
    public function __construct(
        PromotionCode $promotionCode,
        Product $product,
        $type,
        $value,
        $quantityMax = null
    ) {
        $this->promotionCode = $promotionCode;
        $this->product       = $product;
        $this->type          = $type;
        $this->value         = self::TYPE_FREE === $type ? null : $value;
        $this->quantityMax   = $quantityMax;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get promotionCode
     *
     * @return PromotionCode
     */
    public function getPromotionCode()
    {
        return $this->promotionCode;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get quantity max
     *
     * @return null|int
     */
    public function getQuantityMax()
    {
        return $this->quantityMax;
    }

    /**
     * @param string   $type
     * @param float    $value
     * @param null|int $quantityMax
     *
     * @return Promotion
     */
    public function update($type, $value, $quantityMax = null)
    {
        $this->type        = $type;
        $this->value       = self::TYPE_FREE === $type ? null : $value;
        $this->quantityMax = $quantityMax;

        return $this;
    }

    /**
     * @return float|int
     */
    public function getDiscount()
    {
        $discount = 0;

        switch ($this->type) {
            case self::TYPE_PERCENT_OFF:
                $discount = round($this->product->getUnitPrice() * $this->getValue() / 100, 2);
                break;
            case self::TYPE_VALUE_OFF:
                $discount = $this->getValue();
                break;
            case self::TYPE_FREE:
                $discount = $this->product->getUnitPrice();
                break;
        }

        return $discount;
    }

    /**
     * @param CartRow $cartRow
     *
     * @return int
     */
    public function getQuantityForCartRow(CartRow $cartRow)
    {
        if ($cartRow->getQuantity() <= $this->getQuantityMax() || null === $this->getQuantityMax()) {
            return $cartRow->getQuantity();
        }

        return $this->getQuantityMax();
    }

    /**
     * @return bool
     */
    public function isPercentOff()
    {
        return self::TYPE_PERCENT_OFF === $this->type;
    }

    /**
     * @return bool
     */
    public function isValueOff()
    {
        return self::TYPE_VALUE_OFF === $this->type;
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public static function isTypeValueOff($type)
    {
        return self::TYPE_VALUE_OFF === $type;
    }

    /**
     * @return bool
     */
    public function isFree()
    {
        return self::TYPE_FREE === $this->type;
    }

    public function getDiscountAmountForProduct(Product $product, int $quantity)
    {
        if ($this->getProduct() !== $product) {
            return 0;
        }

        if ($quantity <= 0) {
            return 0;
        }

        if (self::TYPE_VALUE_OFF === $this->getType()) {
            return -1 * $this->getDiscount();
        }

        if ($quantity < $this->getQuantityMax() || null === $this->getQuantityMax()) {
            return -1 * $quantity * $this->getDiscount();
        }

        return -1 * $this->getQuantityMax() * $this->getDiscount();
    }
}
