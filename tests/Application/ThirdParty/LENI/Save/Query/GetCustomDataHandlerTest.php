<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Query;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\GetCustomData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\GetCustomDataHandler;

class GetCustomDataHandlerTest extends TestCase
{
    public function testHandleWithNoLeniUserId()
    {
        $getCustomDataHandler = new GetCustomDataHandler();
        $this->assertEquals(
            ['whatever' => 'value', 'EvenementOrigine' => 'API'],
            $getCustomDataHandler->handle(new GetCustomData(['whatever' => 'value']))
        );
    }

    public function testHandleWithLeniUserId()
    {
        $getCustomDataHandler = new GetCustomDataHandler();
        $this->assertEquals(
            ['Id' => 'GLP971', 'whatever' => 'value'],
            $getCustomDataHandler->handle(new GetCustomData(['Id' => 'GLP971', 'whatever' => 'value']))
        );
    }
}
