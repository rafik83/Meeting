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

class Choice
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var array
     */
    private $options;

    /**
     * Choice constructor.
     *
     * @param string $value
     * @param array  $template
     */
    public function __construct($value, array $template)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['label']);
        $resolver->setDefined(['position']);
        $resolver->setDefaults([
            'unitPrice'   => 0,
            'description' => '',
        ]);
        $resolver->setAllowedTypes('label', ['string', 'array']);
        $resolver->setAllowedTypes('unitPrice', ['int', 'float']);
        $resolver->setAllowedTypes('description', ['string', 'array']);
        $resolver->setAllowedTypes('position', ['int']);

        $this->value   = $value;
        $this->options = $resolver->resolve($template);
    }

    /**
     * @param array  $choices
     * @param string $key
     *
     * @return Choice
     * @throws ChoiceNotFoundException
     */
    public static function createFromChoices(array $choices, $key)
    {
        if (!isset($choices[$key])) {
            throw new ChoiceNotFoundException($key, array_keys($choices));
        }

        return new Choice($key, $choices[$key]);
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getLabel($locale)
    {
        return $this->getLocalizedOption('label', $locale);
    }

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale)
    {
        return $this->getLocalizedOption('description', $locale);
    }

    /**
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->options['unitPrice'];
    }

    /**
     * @param string $option
     * @param string $locale
     *
     * @return string
     */
    protected function getLocalizedOption($option, $locale)
    {
        return is_array($this->options[$option]) ?
            (isset($this->options[$option][$locale]) ? $this->options[$option][$locale] : '') :
            $this->options[$option];
    }
}
