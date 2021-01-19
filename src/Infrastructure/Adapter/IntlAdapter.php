<?php

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
        return Intl::getRegionBundle()->getCountryName(\mb_strtoupper($countryCode), $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales(): array
    {
        return Intl::getLocaleBundle()->getLocales();
    }

    /**
     * {@inheritdoc}
     */
    public function currencySymbol(string $currency, ?string $locale = null): ?string
    {
        return Intl::getCurrencyBundle()->getCurrencySymbol($currency, $locale);
    }
}
