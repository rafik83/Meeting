<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface EngineInterface
{
    /**
     * Renders a template.
     *
     * @param string $filePath   A template name
     * @param array  $parameters An array of parameters to pass to the template
     *
     * @return string The evaluated template as a string
     */
    public function render(string $filePath, array $parameters = []): string;
}
