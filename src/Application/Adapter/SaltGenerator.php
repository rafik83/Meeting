<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

class SaltGenerator implements SaltGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        return sha1(uniqid());
    }
}
