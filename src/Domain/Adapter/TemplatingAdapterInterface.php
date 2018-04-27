<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Adapter;

interface TemplatingAdapterInterface
{
    /**
     * Renders a template
     *
     * @param string $template
     * @param array  $context  The arguments
     *
     * @return string
     */
    public function render($template, array $context);
}
