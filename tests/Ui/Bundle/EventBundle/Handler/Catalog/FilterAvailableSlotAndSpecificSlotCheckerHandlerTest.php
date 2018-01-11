<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Catalog;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Domain\Event\Day\DDayGuesser;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;

class FilterAvailableSlotAndSpecificSlotCheckerHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $dDayGuesser;

    /** @var ObjectProphecy */
    private $queryBus;

    /** @var ObjectProphecy */
    private $meetingSlotRepository;

    public function setUp()
    {
        $this->dDayGuesser = $this->prophesize(DDayGuesser::class);
        $this->queryBus = $this->prophesize(QueryBusInterface::class);
        $this->meetingSlotRepository = $this->prophesize(MeetingSlotRepositoryInterface::class);
    }
}
