<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Row;

use Proximum\Vimeet\Application\Components\Template\Exception\ChoiceNotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibChoiceRow extends AbstractLib
{
    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired([
            'choices',
        ]);

        $resolver->setDefined([
            'placeholder',
        ]);

        $resolver->setAllowedTypes('choices', ['array']);
        $resolver->setAllowedTypes('placeholder', ['string', 'array']);
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayableValue($value, $locale)
    {
        return $value ? $this->getChoice($value)->getLabel($locale) : null;
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        $choices = [];

        foreach ($this->options['choices'] as $key => $choice) {
            if (isset($choice['choices'])) {
                $choices = array_merge($choices, $choice['choices']);
            } else {
                $choices[$key] = $choice;
            }
        }

        return $choices;
    }

    /**
     * @param string $key
     *
     * @return Choice
     * @throws ChoiceNotFoundException
     */
    public function getChoice($key)
    {
        $choices = $this->getChoices();

        if (!isset($choices[$key])) {
            throw new ChoiceNotFoundException($key, array_keys($choices));
        }

        return new Choice($key, $choices[$key]);
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getPlaceholder($locale)
    {
        return $this->getLocalizedOption('placeholder', $locale);
    }
}
