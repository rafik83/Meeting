<?php

namespace Proximum\Vimeet\Application\Adapter;

interface LocaleHelperInterface
{
    /**
     * Convert the locale code to the full name of the locale in the given locale
     *
     * @param string      $code
     * @param string|null $locale
     *
     * @return string
     */
    public function language($code, $locale = null): string;
}
