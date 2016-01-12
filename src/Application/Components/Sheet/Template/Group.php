<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownOptionException;
use Proximum\Vimeet\Application\Components\Sheet\Template\Exception\UnknownTypeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Group
{
    /**
     * @var TypeInterface[]
     */
    private $types = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['label', 'template']);
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
     * @param string        $name
     * @param TypeInterface $type
     */
    public function addType($name, TypeInterface $type)
    {
        $this->types[$name] = $type;
    }

    /**
     * @param string $option
     *
     * @return mixed
     * @throws UnknownOptionException
     */
    private function getOption($option)
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
     * @param string $name
     *
     * @return TypeInterface
     * @throws UnknownTypeException
     */
    public function getType($name)
    {
        if (!isset($this->types[$name])) {
            throw new UnknownTypeException($name, array_keys($this->types));
        }

        return $this->types[$name];
    }

    /**
     * Get types
     *
     * @return TypeInterface[]
     */
    public function getTypes()
    {
        return $this->types;
    }
}
