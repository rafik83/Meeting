<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Template;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class Row
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Row constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->setOptions($options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->options;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'type',
            'label',
        ]);

        $resolver->setDefaults([
            'required'     => false,
            'private'      => false,
            'translatable' => false,
            'position'     => 0,
            'tags'         => [],
        ]);

        $resolver->setAllowedTypes('type', ['string']);
        $resolver->setAllowedTypes('label', ['string', 'array']);
        $resolver->setAllowedTypes('position', ['int']);
        $resolver->setAllowedTypes('required', ['bool']);
        $resolver->setAllowedTypes('private', ['bool']);
        $resolver->setAllowedTypes('translatable', ['bool']);
        $resolver->setAllowedTypes('tags', ['array']);
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

    /**
     * @param mixed  $value
     * @param string $locale
     *
     * @return mixed
     */
    public function getDisplayableValue($value, $locale)
    {
        return is_array($value) && $this->isTranslatable() ? $value[$locale] : $value;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->options['type'];
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
     * @return int
     */
    public function getPosition()
    {
        return $this->options['position'];
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->options['required'];
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return $this->options['private'];
    }

    /**
     * @return bool
     */
    public function isTranslatable()
    {
        return $this->options['translatable'];
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->options['tags'];
    }

    /**
     * @param string $tag
     *
     * @return bool
     */
    public function hasTag($tag)
    {
        return in_array($tag, $this->getTags());
    }
}
