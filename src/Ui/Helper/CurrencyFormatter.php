<?php

namespace Proximum\Vimeet\Ui\Helper;

use Twig\Extensions\IntlExtension;

class CurrencyFormatter
{
    private IntlExtension $helper;

    public function __construct(IntlExtension $helper)
    {
        $this->helper = $helper;
    }

    public function format(float $number, ?string $currency = null, ?string $locale = null): ?string
    {
        foreach ($this->helper->getFilters() as $filter) {
            if ('localizedcurrency' === $filter->getName()) {
                $callbable = $filter->getCallable();

                return $callbable($number, $currency, $locale);
            }
        }

        return null;
    }
}
