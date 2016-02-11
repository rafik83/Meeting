<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface TranslatorInterface
{
    /**
     * @param string $id
     * @param array  $parameters
     * @param string $domain
     * @param string $locale
     *
     * @return string
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null);

    /**
     * @param string $id
     * @param int    $number
     * @param array  $parameters
     * @param string $domain
     * @param string $locale
     *
     * @return string
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null);
}
