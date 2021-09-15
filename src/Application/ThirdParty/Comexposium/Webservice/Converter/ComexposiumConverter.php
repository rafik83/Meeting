<?php

namespace Proximum\Vimeet\Application\ThirdParty\Comexposium\Webservice\Converter;

use League\ISO3166\ISO3166;

abstract class ComexposiumConverter
{
    /**
     * @param string $countryAlpha3Code
     *
     * @return null|string
     */
    protected function convertAlpha3ToAlpha2CodeCountry(string $countryAlpha3Code): ?string
    {
        try {
            $country = (new ISO3166())->alpha3($countryAlpha3Code);

            return $country[ISO3166::KEY_ALPHA2];
        } catch (\Exception $exception) {
        }

        return null;
    }

    /**
     * @param array|\stdClass $data
     *
     * @return array
     */
    protected function convertToArray($data): array
    {
        return \is_array($data) ? $data : [$data];
    }

    /**
     * @param string $language
     *
     * @return string
     */
    protected function convertLocale(string $language): string
    {
        return 'FRA' === $language ? 'fr' : 'en';
    }
}
