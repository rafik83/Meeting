<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\IntlInterface;
use Symfony\Component\Intl\Intl;

class IntlAdapter implements IntlInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCountryName($countryCode, $locale = null)
    {
        return Intl::getRegionBundle()->getCountryName($countryCode, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales(): array
    {
        return Intl::getLocaleBundle()->getLocales();
    }
}
