<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\IntlAdapterInterface;
use Symfony\Component\Intl\Intl;

class IntlAdapter implements IntlAdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCountryName($countryCode, $locale = null)
    {
        return Intl::getRegionBundle()->getCountryName($countryCode, $locale);
    }
}
