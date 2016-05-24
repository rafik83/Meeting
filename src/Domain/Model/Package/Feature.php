<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model\Package;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Package;

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
     * @var Package
     */
    private $package;

    /**
     * @var ArrayCollection
     */
    private $translations;

    /**
     * @param Package $package
     */
    public function __construct(Package $package)
    {
        $this->package      = $package;
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
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
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
}
