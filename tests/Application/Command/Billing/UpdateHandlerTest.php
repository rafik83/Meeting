<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Billing;

use Proximum\Vimeet\Application\Command\Billing\Update;
use Proximum\Vimeet\Application\Command\Billing\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $event = new Event();
        $event->setBillingTemplate([['company'], ['vat']]);

        $type  = new Type($event);
        $sheet = new Sheet($event, $type, [], [], new \DateTime());

        $expectedSheet = new Sheet($event, $type, [], [], new \DateTime());
        $expectedSheet->setBillingData(['CompanyName', '1234']);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository->set($expectedSheet)->shouldBeCalled();

        $update  = new Update($sheet, ['CompanyName', '1234']);
        $handler = new UpdateHandler($sheetRepository->reveal());
        $handler->handle($update);
    }
}
