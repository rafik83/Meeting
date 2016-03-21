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
        return [
            new \Twig_SimpleFilter('html', [$this, 'html'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('format_data', [$this, 'formatData']),
            new \Twig_SimpleFilter('choices_list', [$this, 'choicesList'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('boolean_tick', [$this, 'booleanTick'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public function html($value)
    {
        return $value;
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
                    if (isset($choices[$value]['label'][$locale])) {
                        return $choices[$value]['label'][$locale];
                    } elseif (isset($choices[$key]['choices'])
                        && isset($choices[$key]['choices'][$value]['label'][$locale])
                    ) {
                        return $choices[$key]['choices'][$value]['label'][$locale];
                    }
                }
            }
        }

        return $value;
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function booleanTick($value)
    {
        if (true === $value) {
            return '&#10003;';
        }

        return $value;
    }

    /**
     * @param array  $choices
     * @param string $locale
     *
     * @return string
     */
    public function choicesList($choices, $locale)
    {
        if (!count($choices)) {
            return [];
        }

        foreach ($choices as $choice) {
            if (isset($choice['choices']) && isset($choice['label'][$locale])) {
                $items[] = sprintf(
                    '%s%s',
                    $choice['label'][$locale],
                    $this->choicesList($choice['choices'], $locale)
                );
            } elseif (isset($choice['label'][$locale])) {
                $items[] = $choice['label'][$locale];
            }
        }

        asort($items);

        return sprintf('<ul><li>%s</li></ul>', implode('</li><li>', $items));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'app_extension';
    }
}
