<?php

namespace Proximum\Vimeet\Domain\Model;

class ProductTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $heading;

    /**
     * @var string
     */
    private $description;

    /**
     * Small addon text dipslayed under the price
     *
     * @var string
     */
    private $addon;

    /**
     * "Message d'aide si soumis à validation"
     *
     * @var string
     */
    private $subjectedToValidationHelp;

    /**
     * @param Product $product
     * @param string  $locale
     * @param string  $title
     * @param string  $heading
     * @param string  $description
     * @param string  $addon
     * @param string  $subjectedToValidationHelp
     */
    public function __construct(Product $product, $locale, $title, $heading, $description, $addon, $subjectedToValidationHelp)
    {
        $this->product                   = $product;
        $this->locale                    = $locale;
        $this->title                     = $title;
        $this->heading                   = $heading;
        $this->description               = $description;
        $this->addon                     = $addon;
        $this->subjectedToValidationHelp = $subjectedToValidationHelp;
    }

    /**
     * @param string $title
     * @param string $heading
     * @param string $description
     * @param string $addon
     * @param string $subjectedToValidationHelp
     */
    public function set($title, $heading, $description, $addon, $subjectedToValidationHelp)
    {
        $this->title                     = $title;
        $this->heading                   = $heading;
        $this->description               = $description;
        $this->addon                     = $addon;
        $this->subjectedToValidationHelp = $subjectedToValidationHelp;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get heading
     *
     * @return string
     */
    public function getHeading()
    {
        return $this->heading;
    }

    /**
     * Get description
     *
     * @return null|string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get addon
     *
     * @return string
     */
    public function getAddon()
    {
        return $this->addon;
    }

    /**
     * Get subjectedToValidationHelp
     *
     * @return string
     */
    public function getSubjectedToValidationHelp()
    {
        return $this->subjectedToValidationHelp;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'title'       => $this->title,
            'heading'     => $this->heading,
            'description' => $this->description,
            'addon'       => $this->addon,
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
