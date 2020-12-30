<?php

namespace Proximum\Vimeet\Domain\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;

class PromotionCode
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Event
     */
    private $event;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $code;

    /**
     * @var ArrayCollection of Promotion
     */
    private $promotions;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @var \DateTimeInterface
     */
    private $validUntil;

    /**
     * @var int
     */
    private $stock;

    /** @var null|PromotionCodeGroup */
    private $promotionCodeGroup;

    /**
     * PromotionCode constructor.
     *
     * @param Event                   $event
     * @param string                  $title
     * @param string                  $code
     * @param int                     $stock
     * @param \DateTimeInterface      $validUntil
     * @param PromotionCodeGroup|null $promotionCodeGroup
     */
    public function __construct(
        Event $event,
        $title,
        $code,
        $stock = null,
        \DateTimeInterface $validUntil = null,
        ?PromotionCodeGroup $promotionCodeGroup = null
    ) {
        $this->event = $event;
        $this->title = $title;
        $this->code = $code;
        $this->stock = $stock;
        $this->validUntil = $validUntil;
        $this->promotions = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->promotionCodeGroup = $promotionCodeGroup;
    }

    /**
     * @param string $locale
     * @param string $label
     * @param string $description
     *
     * @return PromotionCode
     */
    public function translate($locale, $label, $description)
    {
        if ($this->translations->containsKey($locale)) {
            $this->translations->get($locale)->update($label, $description);
        } else {
            $this->translations->set(
                $locale,
                new PromotionCodeTranslation($this, $locale, $label, $description)
            );
        }

        return $this;
    }

    /**
     * @param string             $title
     * @param string             $code
     * @param int                $stock
     * @param \DateTimeInterface $validUntil
     *
     * @return PromotionCode
     */
    public function update($title, $code, $stock = null, \DateTimeInterface $validUntil = null)
    {
        $this->title      = $title;
        $this->code       = $code;
        $this->stock      = $stock;
        $this->validUntil = $validUntil;

        return $this;
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
     * Get event
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get promotions
     *
     * @return Promotion[]
     */
    public function getPromotions()
    {
        return $this->promotions->toArray();
    }

    /**
     * Get validUntil
     *
     * @return \DateTimeInterface
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Get label
     *
     * @param string $locale
     *
     * @return string
     */
    public function getLabel($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getLabel() : null;
    }

    /**
     * Get description
     *
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getDescription() : null;
    }

    /**
     * Get stock
     *
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param int $stock
     *
     * @return PromotionCode
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @param Product $product
     *
     * @return bool
     */
    public function hasPromotion(Product $product)
    {
        return $this->promotions->exists(function ($key, Promotion $promotion) use ($product) {
            return $promotion->getProduct() === $product;
        });
    }

    /**
     * @param Product $product
     *
     * @return Promotion
     */
    public function getPromotion(Product $product)
    {
        return $this->promotions->filter(function (Promotion $promotion) use ($product) {
            return $promotion->getProduct() === $product;
        })->first();
    }

    /**
     * @param Product  $product
     * @param string   $type
     * @param int      $value
     * @param null|int $quantityMax
     *
     * @return PromotionCode
     */
    public function setPromotion(Product $product, $type, $value, $quantityMax = null)
    {
        if ($this->hasPromotion($product)) {
            $this->getPromotion($product)->update($type, $value, $quantityMax);
        } else {
            $this->promotions->add(new Promotion($this, $product, $type, $value, $quantityMax));
        }

        return $this;
    }

    /**
     * @param Promotion $promotion
     *
     * @return PromotionCode
     */
    public function removePromotion(Promotion $promotion)
    {
        $this->promotions->removeElement($promotion);

        return $this;
    }

    /**
     * @return bool
     */
    public function isSoldOut()
    {
        return 0 === $this->stock;
    }

    /**
     * @param DateTimeInterface $datetime
     *
     * @return bool
     */
    public function isOutDated(DateTimeInterface $datetime)
    {
        if (empty($this->validUntil)) {
            return false;
        }

        return $datetime >= $this->validUntil;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'id'           => $this->getId(),
            'translations' => $this->getTranslationsData(),
        ];
    }

    /**
     * @return array
     */
    public function getTranslationsData()
    {
        $data = [];

        /**
         * @var string $locale
         * @var PromotionCodeTranslation $translation
         */
        foreach ($this->translations->toArray() as $locale => $translation) {
            $data[$locale] = $translation->getData();
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getSerializedData()
    {
        return json_encode($this->getData());
    }

    public function getPromotionCodeGroup(): ?PromotionCodeGroup
    {
        return $this->promotionCodeGroup;
    }

    public function hasPromotionCodeGroup(): bool
    {
        return null !== $this->promotionCodeGroup;
    }
}
