<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda\Admin\Spot;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\MeetingSlotViewQuery;
use Proximum\Vimeet\Application\Query\Agenda\Admin\Spot\MeetingSlotViewQueryHandler;

class MeetingSlotViewQueryHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $locale = 'fr';

        // Mock
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesserCache::class);

        $query   = new MeetingSlotViewQuery($meeting, $locale);
        $handler = new MeetingSlotViewQueryHandler($sheetInfoGuesser->reveal());
    }
}
