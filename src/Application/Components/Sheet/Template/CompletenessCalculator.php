<?php

namespace Proximum\Vimeet\Application\Components\Sheet\Template;

use Proximum\Vimeet\Domain\Model\Template\AbstractTemplate;

/**
 * Calculate the completeness of translations for each locales of a template.
 */
class CompletenessCalculator
{
    /**
     * @param AbstractTemplate $template
     *
     * @return array
     */
    public function compute(AbstractTemplate $template)
    {
        $translatables = $this->getTranslatables($template->getValue());
        $translated    = $this->getTranslated($translatables);
        $countByLocale = $this->countByLocales($template->getLocales(), $translated);

        return $this->convertToPercent($countByLocale, count($translated));
    }

    /**
     * Get translatable values
     *
     * @param array $config
     *
     * @return array
     */
    private function getTranslatables(array $config): array
    {
        if (!isset($config['component'])) {
            return array_reduce($config, function (array $carry, array $component) {
                return array_merge($carry, $this->getTranslatables($component));
            }, []);
        }

        if ('block' === $config['component']) {
            return array_reduce($config['children'], function (array $carry, array $column) {
                return array_merge($carry, $this->getTranslatables($column));
            }, []);
        }

        if ('object' === $config['component']) {
            return array_values(array_filter($config['config'], function ($value, $key) use ($config) {
                return in_array($key, ['label', 'help', 'placeholder'])
                    || 'text' === $config['type'] && 'content' === $key;
            }, ARRAY_FILTER_USE_BOTH));
        }

        throw new \InvalidArgumentException();
    }

    /**
     * Get values translated in at lead one locale
     *
     * @param array $translatables
     *
     * @return array
     */
    private function getTranslated(array $translatables): array
    {
        return array_filter($translatables, function (array $translatable) {
            foreach ($translatable as $locale => $value) {
                if (!empty($value)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Count translations by locales
     *
     * @param array $locales
     * @param array $translated
     *
     * @return array
     */
    private function countByLocales(array $locales, array $translated)
    {
        return array_combine($locales, array_map(function ($locale) use ($translated) {
            return count(array_filter($translated, function ($translations) use ($locale) {
                return !empty($translations[$locale]);
            }));
        }, $locales));
    }

    /**
     * @param array $counts
     * @param int   $max
     *
     * @return array
     */
    private function convertToPercent(array $counts, $max)
    {
        return array_map(function ($count) use ($max) {
            return 0 === $max ? 100 : ($count / $max * 100);
        }, $counts);
    }
}
