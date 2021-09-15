<?php

namespace Proximum\Vimeet\Domain\Model\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Product;

/**
 * Prestation incluse dans une formule
 */
class Feature
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        $this->product      = $product;
        $this->translations = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $locale
     * @param string $title
     * @param string $description
     *
     * @return Feature
     */
    public function translate($locale, $title, $description)
    {
        if ($this->translations->get($locale)) {
            $this->translations->get($locale)->set($title, $description);
        } else {
            $this->translations->set($locale, new FeatureTranslation($this, $locale, $title, $description));
        }

        return $this;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getTitle($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getTitle() : '';
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->translations->containsKey($locale) ? $this->translations->get($locale)->getDescription() : '';
    }
}
