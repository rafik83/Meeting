<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter;

/**
 * This class convert the given locale to a locale with the correct format for the Comexposium API
 */
class LocaleConverter
{
    public function formatLocale(string $locale): string
    {
        return 'fr' === $locale ? 'fre-FR' : 'eng-GB';
    }
}
