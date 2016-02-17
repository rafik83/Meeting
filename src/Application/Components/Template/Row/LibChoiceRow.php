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
use Proximum\Vimeet\Application\Components\Template\Row;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LibChoiceRow extends Row
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
        return $this->options['choices'];
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
