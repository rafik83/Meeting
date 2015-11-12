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
     * @param string $locale
     *
     * @return string
     */
    public function formatData($value, $fieldTemplate, $locale)
    {
        if (isset($fieldTemplate['type']) && isset($value)) {
            if ('lib_country' === $fieldTemplate['type']) {
                return $this->localeHelper->country($value, $locale);
            } elseif ('lib_choice' === $fieldTemplate['type']) {
                $choices = $fieldTemplate['choices'];

                foreach ($choices as $key => $choice) {
                    if (isset($choices[$value]['label'])) {
                        return $choices[$value]['label'][$locale];
                    } elseif (isset($choices[$key]['items'])) {
                        if (isset($choices[$key]['items'][$value])) {
                            return $choices[$key]['items'][$value]['label'][$locale];
                        }
                    }
                }

                return;
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
