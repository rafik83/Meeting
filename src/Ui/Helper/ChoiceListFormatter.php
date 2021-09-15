<?php

namespace Proximum\Vimeet\Ui\Helper;

class ChoiceListFormatter
{
    /**
     * @param array  $choices
     * @param string $locale
     *
     * @return array|string
     */
    public function format(array $choices, $locale)
    {
        if (!count($choices)) {
            return [];
        }

        $items = [];

        foreach ($choices as $choice) {
            if (isset($choice['choices']) && isset($choice['label'][$locale])) {
                $items[] = sprintf('%s%s', $choice['label'][$locale], $this->format($choice['choices'], $locale));
            } elseif (isset($choice['label'][$locale])) {
                $items[] = $choice['label'][$locale];
            }
        }

        asort($items);

        return sprintf('<ul><li>%s</li></ul>', implode('</li><li>', $items));
    }
}
