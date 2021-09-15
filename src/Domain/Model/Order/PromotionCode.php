<?php

namespace Proximum\Vimeet\Domain\Model\Order;

use Proximum\Vimeet\Domain\Model;
use Proximum\Vimeet\Domain\Model\Order;

class PromotionCode
{
    /** @var int */
    private $id;

    /** @var Order */
    private $order;

    /** @var Model\PromotionCode */
    private $promotionCode;

    /** @var string */
    private $data;

    /** @var float */
    private $price;

    /** @var float */
    private $vatRate;

    /** @var Model\Product */
    private $product;

    /**
     * @param Order               $order
     * @param Model\PromotionCode $promotionCode
     * @param float               $price
     * @param float               $vatRate
     */
    public function __construct(Order $order, Model\PromotionCode $promotionCode, float $price, Model\Product $product, float $vatRate)
    {
        $this->order = $order;
        $this->promotionCode = $promotionCode;
        $this->data = $promotionCode->getSerializedData();
        $this->price = $price;
        $this->product = $product;
        $this->vatRate = $vatRate;
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
     * @return PromotionCode
     */
    public function setOrder($order): PromotionCode
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @param float $price
     *
     * @return PromotionCode
     */
    public function setPrice(float $price): PromotionCode
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getVatRate(): float
    {
        return $this->vatRate;
    }

    /**
     * @return Model\PromotionCode
     */
    public function getPromotionCode(): Model\PromotionCode
    {
        return $this->promotionCode;
    }

    public function getProduct(): Model\Product
    {
        return $this->product;
    }

    /**
     * @param string      $locale
     * @param string|null $fallback
     *
     * @return string
     */
    public function getLabel($locale, $fallback = null): string
    {
        return $this->getTranslatedValueOfData('label', $locale, $fallback);
    }

    /**
     * @param string      $locale
     * @param string|null $fallback
     *
     * @return string
     */
    public function getDescription($locale, $fallback = null): string
    {
        return $this->getTranslatedValueOfData('description', $locale, $fallback);
    }

    /**
     * @param Model\Product $product
     *
     * @return $this
     */
    public function setProduct(Model\Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @param string      $value
     * @param string      $locale
     * @param string|null $fallback
     *
     * @return string
     */
    private function getTranslatedValueOfData($value, $locale, $fallback = null): string
    {
        $data = json_decode($this->data, true);

        if (!isset($data['translations'])) {
            return '';
        }

        if (isset($data['translations'][$locale]) && isset($data['translations'][$locale][$value])) {
            return $data['translations'][$locale][$value];
        }

        if (null !== $fallback
            && isset($data['translations'][$fallback])
            && isset($data['translations'][$fallback][$value])
        ) {
            return $data['translations'][$fallback][$value];
        }

        return '';
    }
}
