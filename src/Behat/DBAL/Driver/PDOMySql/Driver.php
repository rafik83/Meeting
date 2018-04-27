<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Behat\DBAL\Driver\PDOMySql;

use Doctrine\DBAL\Driver\PDOMySql\Driver as BaseDriver;

class Driver extends BaseDriver
{
    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return new Platform();
    }
}
