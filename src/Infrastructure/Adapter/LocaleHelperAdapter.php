<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\LocaleHelperInterface;
use Sonata\IntlBundle\Templating\Helper\LocaleHelper;

class LocaleHelperAdapter implements LocaleHelperInterface
{
    /** @var LocaleHelper */
    private $localeHelper;

    /**
     * @param LocaleHelper $localeHelper
     */
    public function __construct(LocaleHelper $localeHelper)
    {
        $this->localeHelper = $localeHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function language($code, $locale = null): string
    {
        return $this->localeHelper->language($code, $locale);
    }
}
