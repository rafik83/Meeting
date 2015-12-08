<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Product\Products;

use Symfony\Component\OptionsResolver\OptionsResolver;

class LibChoiceProduct extends AbstractProduct
{
    /**
     * @var ProductInterface[]
     */
    private $choiceParent;

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $optionsResolver)
    {
        parent::configure($optionsResolver);

        $optionsResolver->setRequired(['label', 'unitPrice']);
        $optionsResolver->setDefined([
            'description',
            'includedIn',
        ]);
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getLabel($locale)
    {
        return isset($this->options['label'][$locale]) ? $this->options['label'][$locale] : null;
    }

    /**
     * @param string $locale
     *
     * @return string|null
     */
    public function getDescription($locale)
    {
        return isset($this->options['description'][$locale]) ? $this->options['description'][$locale] : null;
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->options['unitPrice'];
    }

    /**
     * @param LibChoiceWithDescriptionProduct $product
     */
    public function setChoiceParent(LibChoiceWithDescriptionProduct $product)
    {
        $this->choiceParent = $product;
    }
}
