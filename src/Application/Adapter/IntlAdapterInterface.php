<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface IntlAdapterInterface
{
    /**
     * @param string      $countryCode
     * @param string|null $locale
     *
     * @return string|null
     */
    public function getCountryName($countryCode, $locale = null);
}
