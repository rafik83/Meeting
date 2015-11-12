<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Twig;

use Sonata\IntlBundle\Templating\Helper\LocaleHelper;

class AppExtension extends \Twig_Extension
{
    /**
     * @var LocaleHelper
     */
    protected $localeHelper;

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
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('format_data', array($this, 'formatData')),
        );
    }

    /**
     * @param mixed  $value
     * @param string $fieldTemplate
     *
     * @return string
     */
    public function formatData($value, $fieldTemplate)
    {
        if (isset($fieldTemplate['type']) && isset($value)) {
            if ('lib_country' === $fieldTemplate['type']) {
                return $this->localeHelper->country($value);
            } elseif ('lib_nomenclature' === $fieldTemplate['type']) {
                if (isset($value['label'])) {
                    return $value['label'];
                } else {
                    return $value['value'];
                }
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app_extension';
    }
}
