<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template\Type;

use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownOptionException;
use Proximum\Vimeet\Application\Components\Sheet\Template\TypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractType implements TypeInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['label', 'type']);
        $optionsResolver->setDefaults(['required' => false]);
        $optionsResolver->setDefined(['description']);
    }

    /**
     * {@inheritdoc}
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param string $option
     *
     * @return mixed
     * @throws UnknownOptionException
     */
    protected function getOption($option)
    {
        if (!isset($this->options[$option])) {
            throw new UnknownOptionException($option, array_keys($this->options));
        }

        return $this->options[$option];
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel($locale)
    {
        $label = $this->getOption('label');

        return (string) (is_array($label) ? $label[$locale] : $label);
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($locale)
    {
        $description = $this->getOption('description');

        return (string) (is_array($description) ? $description[$locale] : $description);
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return (bool) $this->getOption('required');
    }
}
