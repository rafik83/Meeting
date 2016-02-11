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
     * @var string
     */
    private $name;

    /**
     * @var TypeInterface[]
     */
    private $types = [];

    /**
     * @var array
     */
    private $options = [];

    /**
     * Group constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

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

        return $this;
    }

    /**
     * @param TypeInterface $type
     *
     * @return Group
     */
    public function addType(TypeInterface $type)
    {
        $this->types[$type->getName()] = $type;
        $type->setGroup($this);

        return $this;
    }

    /**
     * @param string $option
     *
     * @throws UnknownOptionException
     * @return mixed
     *
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
     * @throws UnknownTypeException
     * @return TypeInterface
     *
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
