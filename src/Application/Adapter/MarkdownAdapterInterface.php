<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface MarkdownAdapterInterface
{
    /**
     * @param string $text
     *
     * @return string
     */
    public function toHtml($text);
}
