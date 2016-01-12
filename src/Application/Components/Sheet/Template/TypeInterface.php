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
     * @return bool
     */
    public function isRequired();
}
