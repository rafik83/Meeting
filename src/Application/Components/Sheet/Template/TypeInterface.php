<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet\Template;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface TypeInterface
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver);

    /**
     * @param array $options
     *
     * @return TypeInterface
     */
    public function setOptions(array $options);

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getLabel($locale);

    /**
     * @param string $locale
     *
     * @return string
     */
    public function getDescription($locale);

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatableUntil();

    /**
     * @return bool
     */
    public function isUpdatable();

    /**
     * @return bool
     */
    public function isRequired();

    /**
     * @return array
     */
    public function getTags();

    /**
     * @param string $tag
     *
     * @return bool
     */
    public function hasTag($tag);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param Group $group
     *
     * @return TypeInterface
     */
    public function setGroup(Group $group);

    /**
     * @return Group
     */
    public function getGroup();
}
