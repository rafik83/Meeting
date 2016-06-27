<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Package;

use Proximum\Vimeet\Application\Command\Package\Duplicate;
use Proximum\Vimeet\Application\Command\Package\DuplicateHandler;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class DuplicateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTimeImmutable();

        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en']);

        $package = new Package($event, 'Lorem ipsum', $dateTime);
        $package->translate('fr', 'Forfait', 'Participant et planning', 'Options');
        $package->translate('en', 'Plans', 'Participant and Planning', 'Options');

        $expectedPackage = new Package($event, 'Duplicate event', $dateTime);

        $command        = new Duplicate($package);
        $command->event = $event;
        $command->title = 'Duplicate event';

        $packageRepository = $this->prophesize(PackageRepositoryInterface::class);
        $packageRepository->add($expectedPackage)->shouldBeCalled();

        $handler = new DuplicateHandler($packageRepository->reveal(), $dateTime);
        $result = $handler->handle($command);

        $this->assertEquals($expectedPackage, $result);
    }
}
