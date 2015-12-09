<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Library\Choice;

use Proximum\Vimeet\Application\Components\Library\Choice\Exception\LanguageNotFoundException;

class ChoiceBuilder
{
    /**
     * @param array  $choices
     * @param string $locale
     *
     * @return array
     */
    public function buildChoices(array $choices, $locale)
    {
        $translatedChoices = array_map(function ($choice) use ($locale) {
            if (!isset($choice['label'][$locale])) {
                throw new LanguageNotFoundException($locale, array_keys($choice['label']));
            }

            return (string) $choice['label'][$locale];
        }, $choices);

        $reversedChoices = array_flip($translatedChoices);

        ksort($reversedChoices);

        return $reversedChoices;
    }

    /**
     * @param array  $choices
     * @param string $locale
     *
     * @return array
     * @throws LanguageNotFoundException
     */
    public function buildGroupedChoices(array $choices, $locale)
    {
        $translatedChoices = [];

        foreach ($choices as $choice) {
            if (!isset($choice['label'][$locale])) {
                throw new LanguageNotFoundException($locale, array_keys($choice['label']));
            }

            $translatedChoices[$choice['label'][$locale]] = $this->buildChoices($choice['choices'], $locale);
        }

        ksort($translatedChoices);

        return $translatedChoices;
    }

    /**
     * @param array $choices
     *
     * @return bool
     */
    public function areGroupedChoices(array $choices)
    {
        $keys  = array_keys($choices);
        $first = isset($keys[0]) ? $keys[0] : null;

        return $first !== null && isset($choices[$first]['choices']);
    }
}
