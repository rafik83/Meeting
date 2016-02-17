<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Row;

use Proximum\Vimeet\Application\Components\Template\Row;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibCountryRow extends Row
{
    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefined(['placeholder']);
        $resolver->setAllowedTypes('placeholder', ['string', 'array']);
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayableValue($value, $locale)
    {
        return Intl::getRegionBundle()->getCountryName($value, $locale);
    }
}
