<?php

namespace Proximum\Vimeet\Domain\Model;

class PromotionCodeTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var PromotionCode
     */
    private $promotionCode;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $description;

    /**
     * PromotionCodeTranslation constructor.
     *
     * @param PromotionCode $promotionCode
     * @param string        $locale
     * @param string        $label
     * @param string        $description
     */
    public function __construct(PromotionCode $promotionCode, $locale, $label, $description)
    {
        $this->promotionCode = $promotionCode;
        $this->locale        = $locale;
        $this->label         = $label;
        $this->description   = $description;
    }

    /**
     * @param string $label
     * @param string $description
     */
    public function update($label, $description)
    {
        $this->label       = $label;
        $this->description = $description;
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
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'label'       => $this->label,
            'description' => $this->description,
        ];
    }

    /**
     * @return string
     */
    public function getTranslationSerializedData()
    {
        return json_encode($this->getData());
    }
}
