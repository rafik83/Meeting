<?php

namespace Proximum\Vimeet\Ui\Helper;

use Sonata\IntlBundle\Templating\Helper\LocaleHelper;

class DataFormatter
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
     * @param mixed  $value
     * @param string $fieldTemplate
     * @param string $locale
     *
     * @return mixed
     */
    public function format($value, $fieldTemplate, $locale)
    {
        if (isset($fieldTemplate['type']) && isset($value)) {
            if ('lib_country' === $fieldTemplate['type']) {
                return $this->formatCountry($value, $locale);
            }

            if ('lib_choice' === $fieldTemplate['type']) {
                return $this->formatChoice($value, $fieldTemplate, $locale);
            }
        }

        if (is_array($value)) {
            return $this->formatArray($value, $locale);
        }

        return $value;
    }

    /**
     * @param $value
     * @param $locale
     *
     * @return string
     */
    private function formatCountry($value, $locale)
    {
        return $this->localeHelper->country($value, $locale);
    }

    /**
     * @param $value
     * @param $fieldTemplate
     * @param $locale
     *
     * @return string|null
     */
    private function formatChoice($value, $fieldTemplate, $locale)
    {
        $choices = $fieldTemplate['choices'];

        if (is_array($choices)) {
            foreach ($choices as $key => $choice) {
                if (isset($choices[$value]['label'][$locale])) {
                    return $choices[$value]['label'][$locale];
                } elseif (isset($choices[$key]['choices']) && isset($choices[$key]['choices'][$value]['label'][$locale])) {
                    return $choices[$key]['choices'][$value]['label'][$locale];
                }
            }
        }

        return null;
    }

    /**
     * @param $value
     * @param $locale
     *
     * @return string
     */
    private function formatArray($value, $locale)
    {
        if (isset($value[$locale])) {
            return $value[$locale];
        }

        return implode(', ', $value);
    }
}
