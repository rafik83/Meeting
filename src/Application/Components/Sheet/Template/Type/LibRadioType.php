<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template\Type;

use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\NotAvailableException;
use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownOptionException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibRadioType extends AbstractProductType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        parent::configureOptions($optionsResolver);

        $optionsResolver->remove('unitPrice');
        $optionsResolver->setRequired(['choices']);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnitPrice()
    {
        throw new NotAvailableException('Method getUnitPrice not available. Use getChoiceUnitPrice instead.');
    }

    /**
     * @param $value
     *
     * @throws UnknownOptionException
     * @return string
     *
     */
    public function getChoiceUnitPrice($value)
    {
        $choices = $this->getOption('choices');

        return $choices[$value]['unitPrice'];
    }

    /**
     * @param string $value
     * @param string $locale
     *
     * @throws UnknownOptionException
     * @return string
     *
     */
    public function getChoiceLabel($value, $locale)
    {
        $choices = $this->getOption('choices');

        return $choices[$value]['label'][$locale];
    }
}
