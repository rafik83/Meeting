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

class LibChoiceWithDescriptionProduct extends AbstractProduct
{
    /**
     * @var LibChoiceProduct[]
     */
    private $choices;

    /**
     * {@inheritdoc}
     */
    public function configure(OptionsResolver $optionsResolver)
    {
        parent::configure($optionsResolver);

        $optionsResolver->setRequired(['label', 'type', 'choices', 'required']);
        $optionsResolver->setDefined([
            'quantity',
            'description',
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
     * @return string
     */
    public function getRequired()
    {
        return $this->options['required'];
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * @return float|null
     */
    public function getQuantityMin()
    {
        return isset($this->options['quantity'])
        && isset($this->options['quantity']['min'])
            ? $this->options['quantity']['min'] : null;
    }

    /**
     * @return float|null
     */
    public function getQuantityMax()
    {
        return isset($this->options['quantity'])
        && isset($this->options['quantity']['max'])
            ? $this->options['quantity']['max'] : null;
    }

    /**
     * @return float|null
     */
    public function getQuantityRange()
    {
        return isset($this->options['quantity'])
        && isset($this->options['quantity']['range'])
            ? $this->options['quantity']['range'] : 1;
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->options['unitPrice'];
    }

    /**
     * @return array
     */
    public function getOptionChoices()
    {
        return $this->options['choices'];
    }

    /**
     * @return LibChoiceProduct[]
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @param string $key
     *
     * @return null|LibChoiceProduct
     */
    public function getChoice($key)
    {
        foreach ($this->getChoices() as $choice) {
            if ($choice->getKey() === $key) {
                return $choice;
            }
        }

        return null;
    }

    /**
     * @param LibChoiceProduct $product
     */
    public function addChoice(LibChoiceProduct $product)
    {
        $this->choices[] = $product;
    }
}
