<?php

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
