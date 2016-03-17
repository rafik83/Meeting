<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Row;

use Proximum\Vimeet\Application\Components\Template\Exception\NotAvailableException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductRadioRow extends AbstractProduct
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->remove('unitPrice');
        $optionsResolver->setRequired(['choices']);
        $optionsResolver->setAllowedTypes('choices', ['array']);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPrice()
    {
        throw new NotAvailableException('Method getUnitPrice not available. Use getChoiceUnitPrice instead.');
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return $this->options['choices'];
    }

    /**
     * @param $value
     *
     * @return string
     *
     */
    public function getChoiceUnitPrice($value)
    {
        return Choice::createFromChoices($this->getChoices(), $value)->getUnitPrice();
    }

    /**
     * @param string $value
     * @param string $locale
     *
     * @return string
     *
     */
    public function getChoiceLabel($value, $locale)
    {
        return Choice::createFromChoices($this->getChoices(), $value)->getLabel($locale);
    }

    /**
     * @param string $value
     * @param string $locale
     *
     * @return string
     */
    public function getChoiceDescription($value, $locale)
    {
        return Choice::createFromChoices($this->getChoices(), $value)->getDescription($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayableValue($value, $locale)
    {
        return $value ? $this->getChoiceLabel($value['value'], $locale) : null;
    }
}
