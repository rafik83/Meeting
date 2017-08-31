<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Agenda;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class SheetsAvailableBySlotQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $sheetRepository;

    /** @var ObjectProphecy */
    private $participantRepository;

    /** @var ObjectProphecy */
    private $requestRepository;

    public function setUp()
    {
        $this->sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $this->participantRepository = $this->prophesize(ParticipantRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
    }

    public function testHandle()
    {

    }
}
