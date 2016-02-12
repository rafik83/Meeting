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
use Proximum\Vimeet\Application\Components\Sheet\Template\Group;
use Proximum\Vimeet\Application\Components\Sheet\Template\TypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractType implements TypeInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * AbstractType constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name  = $name;
    }

    /**
     * {@inheritdoc}
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
        $optionsResolver->setRequired(['label', 'type']);
        $optionsResolver->setDefaults(
            [
                'required'       => false,
                'private'        => false,
                'tags'           => [],
                'updatableUntil' => null,
            ]
        );
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
     * @param string $option
     *
     * @throws UnknownOptionException
     * @return mixed
     *
     */
    protected function getOption($option)
    {
        if (!array_key_exists($option, $this->options)) {
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
    public function getUpdatableUntil()
    {
        return $this->getOption('updatableUntil') ? new \DateTime($this->getOption('updatableUntil')) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function isUpdatable()
    {
        return $this->getUpdatableUntil() > new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function isRequired()
    {
        return (bool) $this->getOption('required');
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        return (array) $this->getOption('tags');
    }

    /**
     * {@inheritdoc}
     */
    public function hasTag($tag)
    {
        return in_array($tag, $this->getTags());
    }

    /**
     * {@inheritdoc}
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup()
    {
        return $this->group;
    }
}
