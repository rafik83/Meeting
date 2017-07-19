<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface IntlInterface
{
    /**
     * @param string      $countryCode
     * @param string|null $locale
     *
     * @return string|null
     */
    public function getCountryName($countryCode, $locale = null);

    /**
     * Get an array of available locales code:
     * either the two letter ISO 639-1 language code (e.g. fr),
     * or the language code followed by an underscore (_), then the ISO 3166-1 alpha-2 country code
     * (e.g. fr_FR for French/France).
     *
     * @return array
     */
    public function getLocales(): array;
}
