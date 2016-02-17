<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template\Row;

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
        $resolver->setAllowedTypes('label', ['string', 'array']);

        $this->value   = $value;
        $this->options = $resolver->resolve($template);
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
