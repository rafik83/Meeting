<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Package;

use Prophecy\Argument;
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
        $defaultLabels = [
            'plans' => [
                'fr' => 'Forfait',
                'en' => 'Plans',
            ],
            'participant_and_planning' => [
                'fr' => 'Participant et planning',
                'en' => 'Participant and Planning',
            ],
            'options' => [
                'fr' => 'Options',
                'en' => 'Options',
            ],
        ];

        $dateTime = new \DateTimeImmutable();

        $event = new Event();
        $event->setLocales(['fr', 'en']);

        $expected = new Package($event, 'Lorem ipsum', $dateTime);
        $expected->translate('fr', 'Forfait', 'Participant et planning', 'Options');
        $expected->translate('en', 'Plans', 'Participant and Planning', 'Options');

        $command        = new Create();
        $command->event = $event;
        $command->title = 'Lorem ipsum';

        $packageRepository = $this->prophesize(PackageRepositoryInterface::class);
        $dateTime          = new \DateTimeImmutable();

        $packageRepository->add(Argument::that(function (Package $package) use ($expected) {
            $this->assertEquals($expected, $package);

            return true;
        }))->shouldBeCalled();

        $handler = new CreateHandler($packageRepository->reveal(), $dateTime, $defaultLabels);

        $this->assertEquals(new CreateResult($expected), $handler->handle($command));
    }
}
