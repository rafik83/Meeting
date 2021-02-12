<?php

namespace Proximum\Vimeet\Ui\Helper;

class CurrencyFormatter
{
    /**
     * @var \Twig_Extensions_Extension_Intl
     */
    private $helper;

    /**
     * CurrencyFormatter constructor.
     *
     * @param \Twig_Extensions_Extension_Intl $helper
     */
    public function __construct($helper)
    {
        $this->helper = $helper;
    }

    /**
     * @param float       $number
     * @param string|null $currency
     * @param string|null $locale
     *
     * @return string
     */
    public function format($number, $currency = null, $locale = null)
    {
        //TODO migrate to new version of twig intl filter
        foreach ($this->helper->getFilters() as $filter) {
            if ('localizedcurrency' === $filter->getName()) {
                $callbable = $filter->getCallable();

                return $callbable($number, $currency, $locale);
            }
        }

        return null;
    }
}
