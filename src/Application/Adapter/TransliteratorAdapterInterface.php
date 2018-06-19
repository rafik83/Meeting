<?php

/*
 * This file is part of the PhpStorm project.
 *
 * Copyright (C) PhpStorm
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface TransliteratorAdapterInterface
{
    public function urlize(array $parameters): string;
}
