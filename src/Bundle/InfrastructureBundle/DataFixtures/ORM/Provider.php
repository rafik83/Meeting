<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\DataFixtures\ORM;

class Provider
{
    private $domain;

    public function __construct($domain)
    {
        $this->domain = $domain;
    }

    public function domain()
    {
        return $this->domain;
    }
}
