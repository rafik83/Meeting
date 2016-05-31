<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Package;

use Proximum\Vimeet\Application\Command\Package\Create;
use Proximum\Vimeet\Application\Command\Package\CreateHandler;
use Proximum\Vimeet\Application\Command\Package\CreateResult;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class CreateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTimeImmutable();

        $event = new Event();
        $event->setLocales(['fr', 'en']);

        $package = new Package($event, 'Lorem ipsum', $dateTime);
        $package->translate('fr', 'Forfait', 'Participant et planning', 'Options');
        $package->translate('en', 'Forfait', 'Participant et planning', 'Options');

        $command        = new Create();
        $command->event = $event;
        $command->title = 'Lorem ipsum';

        $packageRepository = $this->prophesize(PackageRepositoryInterface::class);
        $dateTime          = new \DateTimeImmutable();

        $packageRepository->add($package)->shouldBeCalled();

        $handler = new CreateHandler($packageRepository->reveal(), $dateTime);

        $this->assertEquals(new CreateResult($package), $handler->handle($command));
    }
}
